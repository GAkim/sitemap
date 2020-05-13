<?php
/**
 * @category  ScandiPWA
 * @package   ScandiPWA\Sitemap
 * @author    Ilja Lapkovskis <info@scandiweb.com / ilja@scandiweb.com>
 * @copyright Copyright (c) 2019 Scandiweb, Ltd (http://scandiweb.com)
 * @license   OSL-3.0
 */

namespace ScandiPWA\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\ObjectManager;
use Magento\Sitemap\Controller\Adminhtml\Sitemap\Generate as SourceGenerate;

/**
 * Generate sitemap file
 */
class Generate extends SourceGenerate implements HttpGetActionInterface
{
    /** @var \Magento\Store\Model\App\Emulation $appEmulation */
    private $appEmulation;

    /**
     * Generate constructor.
     * @param Action\Context $context
     * @param \Magento\Store\Model\App\Emulation|null $appEmulation
     */
    public function __construct(
        Action\Context $context,
        Emulation $appEmulation = null
    ) {
        parent::__construct($context);
        $this->appEmulation = $appEmulation ?: ObjectManager::getInstance()
            ->get(\Magento\Store\Model\App\Emulation::class);
    }

    /**
     * Generate sitemap
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = $this->_objectManager->create(\Magento\Sitemap\Model\Sitemap::class);
        /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
        $sitemap->load($id);
        if ($sitemap->getId()) {
            try {
                $this->appEmulation->startEnvironmentEmulation(
                    $sitemap->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $sitemap->generateXml();
                $this->appEmulation->stopEnvironmentEmulation();
                $this->messageManager->addSuccessMessage(
                    __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t generate the sitemap right now.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to generate.'));
        }

        $this->_redirect('adminhtml/*/');
    }
}
