<?php
/**
* @category Oscp
* @package Oscp_ImageGallery
*/ 

class Oscp_ImageGallery_Adminhtml_ImagegalleryController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()->_setActiveMenu('imagegallery/items')->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('imagegallery/imagegallery')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('imagegallery_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('imagegallery/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('imagegallery/adminhtml_imagegallery_edit'))->_addLeft($this->getLayout()->createBlock('imagegallery/adminhtml_imagegallery_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('imagegallery')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $id = (int) $this->getRequest()->getParam('id');
            // ---------------------------------------------------------------        

            $model = Mage::getModel('imagegallery/imagegallery');

            $smallimage = $model->setImage('smallimage');
            $largeimage = $model->setImage('largeimage');

            if (!$id && !$smallimage && !$largeimage) {
                Mage::getSingleton('adminhtml/session')->addError('Images are not uploaded for gallery');
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
                return;
            }

            if ($smallimage) {
                $data['smallimage'] = $smallimage;
            }
            if ($largeimage) {
                $data['largeimage'] = $largeimage;
            }
            $model->setData($data)->setId($this->getRequest()->getParam('id'))->setStoreId($this->getRequest()->getParam('store_id'));

            try {
                if ($model->getCreatedTime == NULL) {
                    $model->setCreatedTime(now())->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('imagegallery')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array(
                        'id' => $model->getId()
                    ));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('imagegallery')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('imagegallery/imagegallery')->load($this->getRequest()->getParam('id'));

                if (file_exists(Mage::getBaseDir('media') . DS . $model->getSmallimage())) {
                    unlink(Mage::getBaseDir('media') . DS . $model->getSmallimage());
                }
                if (file_exists(Mage::getBaseDir('media') . DS . $model->getLargeimage())) {
                    unlink(Mage::getBaseDir('media') . DS . $model->getSmallimage());
                }

                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $imagegalleryIds = $this->getRequest()->getParam('imagegallery');
        if (!is_array($imagegalleryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($imagegalleryIds as $imagegalleryId) {
                    $imagegallery = Mage::getModel('imagegallery/imagegallery')->load($imagegalleryId);

                    if (file_exists(Mage::getBaseDir('media') . DS . $imagegallery->getSmallimage())) {
                        unlink(Mage::getBaseDir('media') . DS . $imagegallery->getSmallimage());
                    }
                    if (file_exists(Mage::getBaseDir('media') . DS . $imagegallery->getLargeimage())) {
                        unlink(Mage::getBaseDir('media') . DS . $imagegallery->getSmallimage());
                    }
                    $imagegallery->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($imagegalleryIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $imagegalleryIds = $this->getRequest()->getParam('imagegallery');
        if (!is_array($imagegalleryIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($imagegalleryIds as $imagegalleryId) {
                    $imagegallery = Mage::getSingleton('imagegallery/imagegallery')->load($imagegalleryId)->setStatus($this->getRequest()->getParam('status'))->setIsMassupdate(true)->save();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($imagegalleryIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'imagegallery.csv';
        $content = $this->getLayout()->createBlock('imagegallery/adminhtml_imagegallery_grid')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'imagegallery.xml';
        $content = $this->getLayout()->createBlock('imagegallery/adminhtml_imagegallery_grid')->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}

