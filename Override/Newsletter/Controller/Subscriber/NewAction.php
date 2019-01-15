<?php
/**
 * GiaPhuGroup Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GiaPhuGroup.com license that is
 * available through the world-wide-web at this URL:
 * https://www.giaphugroup.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    PHPCuong
 * @package     PHPCuong_Newsletter
 * @copyright   Copyright (c) 2019-2020 GiaPhuGroup Co., Ltd. All rights reserved. (http://www.giaphugroup.com/)
 * @license     https://www.giaphugroup.com/LICENSE.txt
 */

namespace PHPCuong\Newsletter\Override\Newsletter\Controller\Subscriber;

use Magento\Framework\App\ObjectManager;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $_resultJson;

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        $result = [];
        $result['error'] = true;
        $result['message'] = __('You are the man.');
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                if ($subscriber->getId()
                    && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    $result['message'] = __('This email address is already subscribed.');
                } else {
                    $status = $this->_subscriberFactory->create()->subscribe($email);
                    if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                        $result['message'] = __('The confirmation request has been sent.');
                        $result['error'] = false;
                    } else {
                        $result['message'] = __('Thank you for your subscription.');
                        $result['error'] = false;
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['message'] = __('There was a problem with the subscription: %1', $e->getMessage());
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }
        return $this->getResultJson()->setData($result);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function getResultJson()
    {
        if ($this->_resultJson === null) {
            $this->_resultJson = ObjectManager::getInstance()->get(\Magento\Framework\Controller\Result\Json::class);
        }
        return $this->_resultJson;
    }
}
