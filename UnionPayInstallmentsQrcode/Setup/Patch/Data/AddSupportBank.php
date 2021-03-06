<?php

declare(strict_types=1);

namespace Zhixing\UnionPayInstallmentsQrcode\Setup\Patch\Data;

/**
 * Class AddSupportBank
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Setup\Patch\Data
 */
class AddSupportBank implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Zhixing\Config\Helper\Data
     */
    protected $configHelper;

    /**
     * @var \Zhixing\Config\Helper\Data
     */
    protected $dataHelper;

    /**
     * InstallCnConfiguration constructor.
     *
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Zhixing\Config\Helper\Data $configHelper
     * @param \Zhixing\Config\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Zhixing\Config\Helper\Data $configHelper,
        \Zhixing\Config\Helper\Data $dataHelper
    ) {
        $this->appState = $appState;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * phpcs:disable Magento2.Files.LineLength.MaxExceeded
     */
    public function apply(): void
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            unset($e);
        }

        $setup = $this->moduleDataSetup;
        $setup->startSetup();

        // only for chinese instance
        if ($this->dataHelper->isChineseInstance()) {
            $configurations = [
                [
                    'scope' => \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    'scope_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    'configs' => [
                        [
                            'path' => 'payment/zhixing_uiq/support_bank',
                            'value' => $this->getBanksJson()
                        ]
                    ]
                ]
            ];

            $this->configHelper->saveConfigurations($configurations);
        }

        $setup->endSetup();
    }

    /**
     * @return false|string
     */
    public function getBanksJson()
    {
        $index = time();
        $banks = [
            $index.'_0' => ["code" => "JZB", "comment" => "????????????"],
            $index.'_1' => ["code" => "NXBANK", "comment" => "????????????"],
            $index.'_2' => ["code" => "ZJNX", "comment" => "????????????"],
            $index.'_3' => ["code" => "ZBCB", "comment" => "????????????"],
            $index.'_4' => ["code" => "HSBC", "comment" => "????????????"],
            $index.'_5' => ["code" => "CABANK", "comment" => "????????????"],
            $index.'_6' => ["code" => "CGNB", "comment" => "??????????????????"],
            $index.'_7' => ["code" => "SHRCB", "comment" => "??????????????????"],
            $index.'_8' => ["code" => "BCM", "comment" => "????????????"],
            $index.'_9' => ["code" => "SRCB", "comment" => "??????????????????"],
            $index.'_10' => ["code" => "BOZ", "comment" => "????????????"],
            $index.'_11' => ["code" => "GRCB", "comment" => "??????????????????"],
            $index.'_12' => ["code" => "BRCB", "comment" => "??????????????????"],
            $index.'_13' => ["code" => "BOJ", "comment" => "????????????"],
            $index.'_14' => ["code" => "BON", "comment" => "????????????"],
            $index.'_15' => ["code" => "BOB", "comment" => "????????????"],
            $index.'_16' => ["code" => "BOS", "comment" => "????????????"],
            $index.'_17' => ["code" => "CIB", "comment" => "????????????"],
            $index.'_18' => ["code" => "CMBC", "comment" => "????????????"],
            $index.'_19' => ["code" => "CCB", "comment" => "????????????"],
            $index.'_20' => ["code" => "BOC", "comment" => "????????????"],
            $index.'_21' => ["code" => "PSBC", "comment" => "????????????"],
            $index.'_22' => ["code" => "CEB", "comment" => "????????????"],
            $index.'_23' => ["code" => "GDB", "comment" => "????????????"],
            $index.'_24' => ["code" => "HXBANK", "comment" => "????????????"],
            $index.'_25' => ["code" => "ICBC", "comment" => "????????????"],
            $index.'_26' => ["code" => "SPDB", "comment" => "????????????"],
            $index.'_27' => ["code" => "CITIC", "comment" => "????????????"],
            $index.'_28' => ["code" => "ABC", "comment" => "????????????"],
            $index.'_29' => ["code" => "CMB", "comment" => "????????????"],
            $index.'_30' => ["code" => "PAB", "comment" => "????????????"],
        ];

        return json_encode($banks, 256);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [\Zhixing\Config\Setup\Patch\Data\InstallCnWebsite::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
