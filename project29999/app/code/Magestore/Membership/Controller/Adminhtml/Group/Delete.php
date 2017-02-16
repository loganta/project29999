<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 26/5/2016
 * Time: 11:48 AM
 */

namespace Magestore\Membership\Controller\Adminhtml\Group;

class Delete extends \Magestore\Membership\Controller\Adminhtml\Group
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
                /** @var \Magestore\Membership\Model\Group $model */
                $model = $this->_objectManager->create('Magestore\Membership\Model\Group');
                $model->load($id)->delete();
                $this->messageManager->addSuccess(__('You deleted the Product Group'));

                return $resultRedirect->setPath('*/*/');

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [$this->_param_crud_id => $id]);
            }
        }

        $this->messageManager->addError(__('We can\'t find a Product Group to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}