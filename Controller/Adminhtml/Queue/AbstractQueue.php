<?php
/**
 * BSeller Platform | B2W - Companhia Digital
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  ${MAGENTO_MODULE_NAMESPACE}
 * @package   ${MAGENTO_MODULE_NAMESPACE}_${MAGENTO_MODULE}
 *
 * @copyright Copyright (c) 2018 B2W Digital - BSeller Platform. (http://www.bseller.com.br)
 *
 * @author    Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Queue;

use BitTools\SkyHub\Api\QueueRepositoryInterface;
use BitTools\SkyHub\Controller\Adminhtml\AbstractController;
use Magento\Backend\App\Action\Context as BackendContext;
use BitTools\SkyHub\Helper\Context as HelperContext;

abstract class AbstractQueue extends AbstractController
{
    
    /** @var QueueRepositoryInterface */
    protected $queueRepository;
    
    
    /**
     * AbstractQueue constructor.
     *
     * @param BackendContext           $context
     * @param HelperContext            $helperContext
     * @param QueueRepositoryInterface $queueRepository
     */
    public function __construct(
        BackendContext $context,
        HelperContext $helperContext,
        QueueRepositoryInterface $queueRepository
    )
    {
        parent::__construct($context, $helperContext);
        $this->queueRepository = $queueRepository;
    }
}