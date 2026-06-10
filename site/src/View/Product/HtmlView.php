<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Product;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $item;
    public $variants = [];
    public $images   = [];

    public function display($tpl = null): void
    {
        $this->item = $this->get('Item');

        if (!$this->item) {
            throw new \Exception('Product not found', 404);
        }

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Load variants and images from DB
        $db = Factory::getContainer()->get('db');

        $vQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_product_variants'))
            ->where($db->quoteName('product_id') . ' = ' . (int) $this->item->id)
            ->order('ordering ASC, id ASC');
        $variants = $db->setQuery($vQuery)->loadObjectList() ?: [];

        foreach ($variants as $variant) {
            $oQuery = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__sanctuaryshop_product_variant_options'))
                ->where($db->quoteName('variant_id') . ' = ' . (int) $variant->id)
                ->order('ordering ASC, id ASC');
            $variant->options = $db->setQuery($oQuery)->loadObjectList() ?: [];
        }
        $this->variants = $variants;

        $iQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_product_images'))
            ->where($db->quoteName('product_id') . ' = ' . (int) $this->item->id)
            ->order('ordering ASC, id ASC');
        $this->images = $db->setQuery($iQuery)->loadObjectList() ?: [];

        $doc  = Factory::getDocument();
        $app  = Factory::getApplication();
        $menu = $app->getMenu()->getActive();

        // Page title
        $title  = $this->item->title;
        if ($menu) {
            $title .= ' | ' . $menu->getParams()->get('page_title', $menu->title);
        }
        $doc->setTitle($title);

        // Meta description
        $desc = mb_substr(strip_tags($this->item->description ?? ''), 0, 160);
        if ($desc) {
            $doc->setDescription($desc);
        }

        // Canonical URL
        $canonical = 'index.php?option=com_sanctuaryshop&view=product&id=' . (int) $this->item->id . ':' . $this->item->alias;
        $doc->addHeadLink(Factory::getUri()->toString(['scheme', 'host']) . \Joomla\CMS\Router\Route::_($canonical), 'canonical');

        // Schema.org JSON-LD
        $price = (float) ($this->item->sale_price ?: $this->item->price);
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $this->item->title,
            'description' => mb_substr(strip_tags($this->item->description ?? ''), 0, 500),
            'sku'         => $this->item->sku,
            'offers'      => [
                '@type'           => 'Offer',
                'url'             => Factory::getUri()->toString(['scheme', 'host']) . \Joomla\CMS\Router\Route::_($canonical),
                'price'           => number_format($price, 2, '.', ''),
                'priceCurrency'   => strtoupper(\Joomla\CMS\Component\ComponentHelper::getParams('com_sanctuaryshop')->get('currency', 'USD')),
                'availability'    => 'https://schema.org/InStock',
                'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
            ],
        ];
        if ($this->item->image) {
            $schema['image'] = Factory::getUri()->toString(['scheme', 'host']) . '/' . ltrim($this->item->image, '/');
        }
        $doc->addCustomTag('<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>');

        parent::display($tpl);
    }
}
