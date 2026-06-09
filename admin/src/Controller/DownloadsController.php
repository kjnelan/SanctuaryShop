<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class DownloadsController extends AdminController
{
    protected $text_prefix = 'COM_SANCTUARYSHOP_DOWNLOADS';

    public function getModel($name = 'Downloads', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function revoke(): void
    {
        $this->checkToken();
        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_map('intval', $cid);

        if (empty($cid)) {
            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&view=downloads', false));
            return;
        }

        $db    = $this->getMVCFactory()->createModel('Downloads', 'Administrator', ['ignore_request' => true])->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sanctuaryshop_download_tokens'))
            ->set($db->quoteName('revoked') . ' = 1')
            ->whereIn($db->quoteName('id'), $cid);
        $db->setQuery($query)->execute();

        $this->setMessage(\Joomla\CMS\Language\Text::plural('COM_SANCTUARYSHOP_N_TOKENS_REVOKED', count($cid)));
        $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&view=downloads', false));
    }

    public function delete(): void
    {
        $this->checkToken();
        $cid = (array) $this->input->get('cid', [], 'array');
        $cid = array_map('intval', $cid);

        if (empty($cid)) {
            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&view=downloads', false));
            return;
        }

        $db    = $this->getMVCFactory()->createModel('Downloads', 'Administrator', ['ignore_request' => true])->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__sanctuaryshop_download_tokens'))
            ->whereIn($db->quoteName('id'), $cid);
        $db->setQuery($query)->execute();

        $this->setMessage(\Joomla\CMS\Language\Text::plural('COM_SANCTUARYSHOP_N_TOKENS_DELETED', count($cid)));
        $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&view=downloads', false));
    }
}
