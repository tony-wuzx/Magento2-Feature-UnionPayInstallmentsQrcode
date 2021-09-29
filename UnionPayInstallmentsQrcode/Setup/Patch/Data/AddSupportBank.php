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
            $index.'_0' => ["code" => "JZB", "comment" => "锦州银行"],
            $index.'_1' => ["code" => "NXBANK", "comment" => "宁夏银行"],
            $index.'_2' => ["code" => "ZJNX", "comment" => "浙江农信"],
            $index.'_3' => ["code" => "ZBCB", "comment" => "齐商银行"],
            $index.'_4' => ["code" => "HSBC", "comment" => "汇丰银行"],
            $index.'_5' => ["code" => "CABANK", "comment" => "长安银行"],
            $index.'_6' => ["code" => "CGNB", "comment" => "四川天府银行"],
            $index.'_7' => ["code" => "SHRCB", "comment" => "上海农商银行"],
            $index.'_8' => ["code" => "BCM", "comment" => "交通银行"],
            $index.'_9' => ["code" => "SRCB", "comment" => "深圳农商银行"],
            $index.'_10' => ["code" => "BOZ", "comment" => "浙商银行"],
            $index.'_11' => ["code" => "GRCB", "comment" => "广州农商银行"],
            $index.'_12' => ["code" => "BRCB", "comment" => "北京农商银行"],
            $index.'_13' => ["code" => "BOJ", "comment" => "江苏银行"],
            $index.'_14' => ["code" => "BON", "comment" => "宁波银行"],
            $index.'_15' => ["code" => "BOB", "comment" => "北京银行"],
            $index.'_16' => ["code" => "BOS", "comment" => "上海银行"],
            $index.'_17' => ["code" => "CIB", "comment" => "兴业银行"],
            $index.'_18' => ["code" => "CMBC", "comment" => "民生银行"],
            $index.'_19' => ["code" => "CCB", "comment" => "建设银行"],
            $index.'_20' => ["code" => "BOC", "comment" => "中国银行"],
            $index.'_21' => ["code" => "PSBC", "comment" => "邮储银行"],
            $index.'_22' => ["code" => "CEB", "comment" => "光大银行"],
            $index.'_23' => ["code" => "GDB", "comment" => "广发银行"],
            $index.'_24' => ["code" => "HXBANK", "comment" => "华夏银行"],
            $index.'_25' => ["code" => "ICBC", "comment" => "工商银行"],
            $index.'_26' => ["code" => "SPDB", "comment" => "浦发银行"],
            $index.'_27' => ["code" => "CITIC", "comment" => "中信银行"],
            $index.'_28' => ["code" => "ABC", "comment" => "农业银行"],
            $index.'_29' => ["code" => "CMB", "comment" => "招商银行"],
            $index.'_30' => ["code" => "PAB", "comment" => "平安银行"],
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
