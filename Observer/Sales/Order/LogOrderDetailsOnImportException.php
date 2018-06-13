<?php

namespace BitTools\SkyHub\Observer\Sales\Order;

use Magento\Framework\Event\Observer;

class LogOrderDetailsOnImportException extends AbstractOrder
{
    
    /**
     * @param Observer $observer
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (true === $this->context->registryManager()->registry('disable_order_log')) {
            return;
        }
        
        /**
         * @var \Exception $exception
         * @var array      $orderData
         */
        $exception = $observer->getData('exception');
        $orderData = (array) $observer->getData('order_data');
        
        if (!$exception || !$orderData) {
            return;
        }
        
        $orderCode = $this->arrayExtract($orderData, 'code');
        
        $data = [
            'entity_id'       => null,
            'reference'       => (string) $orderCode,
            'entity_type'     => \BitTools\SkyHub\Model\Entity::TYPE_SALES_ORDER,
            'status'          => \BitTools\SkyHub\Model\Queue::STATUS_FAIL,
            'process_type'    => \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_IMPORT,
            'messages'        => $exception->getMessage(),
            'additional_data' => json_encode($orderData),
            'can_process'     => false,
            'store_id'        => (int) $this->getStore()->getId(),
        ];
        
        /** @var \BitTools\SkyHub\Api\QueueRepositoryInterface $repository */
        $repository = $this->context->objectManager()->create(\BitTools\SkyHub\Api\QueueRepositoryInterface::class);
        
        /** @var \BitTools\SkyHub\Model\Queue $queue */
        $queue = $repository->create($data);
        $repository->save($queue);
    }
}
