<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

class DownloadController extends BaseController
{
    /**
     * Serve a file via token.
     * URL: index.php?option=com_sanctuaryshop&task=download.get&token=XXXXXXXX
     */
    public function get(): void
    {
        $app   = Factory::getApplication();
        $token = trim($this->input->getString('token', ''));

        if (strlen($token) !== 64 || !ctype_xdigit($token)) {
            $app->enqueueMessage('Invalid download token.', 'error');
            $app->redirect('/');
            return;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('t.*, pf.filename, pf.label, pf.filesize')
            ->from($db->quoteName('#__sanctuaryshop_download_tokens', 't'))
            ->leftJoin($db->quoteName('#__sanctuaryshop_product_files', 'pf') . ' ON pf.id = t.file_id')
            ->where($db->quoteName('t.token') . ' = ' . $db->quote($token));
        $row = $db->setQuery($query)->loadObject();

        if (!$row) {
            $app->enqueueMessage('Download token not found.', 'error');
            $app->redirect('/');
            return;
        }

        if ($row->revoked) {
            $app->enqueueMessage('This download link has been revoked.', 'error');
            $app->redirect('/');
            return;
        }

        if ($row->expires && strtotime($row->expires) < time()) {
            $app->enqueueMessage('This download link has expired.', 'error');
            $app->redirect('/');
            return;
        }

        if ($row->download_count >= $row->max_downloads) {
            $app->enqueueMessage('Maximum download limit reached for this link.', 'error');
            $app->redirect('/');
            return;
        }

        $params   = ComponentHelper::getParams('com_sanctuaryshop');
        $basePath = rtrim($params->get('download_path', ''), '/\\');

        if (empty($basePath)) {
            $app->enqueueMessage('Download path not configured.', 'error');
            $app->redirect('/');
            return;
        }

        $filename = ltrim($row->filename, '/\\');
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $filename;
        $fullPath = realpath($fullPath);

        // Prevent path traversal
        if (!$fullPath || strpos($fullPath, realpath($basePath)) !== 0 || !is_file($fullPath)) {
            $app->enqueueMessage('File not found.', 'error');
            $app->redirect('/');
            return;
        }

        // Increment download count
        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__sanctuaryshop_download_tokens'))
            ->set($db->quoteName('download_count') . ' = ' . $db->quoteName('download_count') . ' + 1')
            ->set($db->quoteName('last_used') . ' = ' . $db->quote(Factory::getDate()->toSql()))
            ->where($db->quoteName('id') . ' = ' . (int) $row->id);
        $db->setQuery($upd)->execute();

        $this->streamFile($fullPath, $row->label ?: basename($filename));
    }

    private function streamFile(string $path, string $downloadName): void
    {
        $size    = filesize($path);
        $mime    = mime_content_type($path) ?: 'application/octet-stream';
        $start   = 0;
        $end     = $size - 1;
        $partial = false;

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
                $start   = $m[1] !== '' ? (int) $m[1] : 0;
                $end     = $m[2] !== '' ? (int) $m[2] : $size - 1;
                $partial = true;
            }
        }

        $length = $end - $start + 1;

        if ($partial) {
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$size");
        } else {
            header('HTTP/1.1 200 OK');
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
        header('Content-Length: ' . $length);
        header('Accept-Ranges: bytes');
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        $fh = fopen($path, 'rb');
        if (!$fh) {
            http_response_code(500);
            exit('Cannot open file.');
        }

        fseek($fh, $start);
        $remaining = $length;
        $chunk     = 1048576; // 1 MB

        while ($remaining > 0 && !feof($fh) && connection_status() === CONNECTION_NORMAL) {
            $read = min($chunk, $remaining);
            echo fread($fh, $read);
            flush();
            $remaining -= $read;
        }

        fclose($fh);
        Factory::getApplication()->close();
    }
}
