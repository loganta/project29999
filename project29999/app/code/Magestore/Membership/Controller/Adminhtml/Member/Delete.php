<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 26/5/2016
 * Time: 11:47 AM
 */

namespace Magestore\Membership\Controller\Adminhtml\Member;

class Delete extends \Magestore\Membership\Controller\Adminhtml\Member
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam($this->_param_crud_id);
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                /** @var \Magestore\Membership\Model\Member $model */
                $model = $this->_objectManager->create('Magestore\Membership\Model\Member');
                $model->load($id)->delete();
                $this->messageManager->addSuccess(__('You deleted the Member'));

                return $resultRedirect->setPath('*/*/');

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [$this->_param_crud_id => $id]);
            }
        }

        $this->messageManager->addError(__('We can\'t find a Member to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}