<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Helper;

/**
 * Class Data
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_directory = null;

    /**
     * @var string
     */
    private $_logDir = 'zhixing_uiq';

    /**
     * @var null
     */
    private $_logFile = null;

    /**
     * @var null
     */
    private $_logType = null;

    /**
     * @var bool|mixed
     */
    private $_debug = false;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        parent::__construct($context);
        $this->_debug = $this->getConfigValue('debug');
        $this->localeDate = $localeDate;
        $this->filesystem = $filesystem;
    }

    /**
     * @return string
     */
    protected function _getBaseDir()
    {
        try {
            if ($this->_directory === null) {
                $this->_directory = $this->filesystem->getDirectoryWrite(
                    \Magento\Framework\App\Filesystem\DirectoryList::LOG
                );
            }
            return $this->_directory->getAbsolutePath();
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            //do nothing here
            return null;
        }
    }

    /**
     * Add log message to the special log file
     * this payment method has himself log file without any other logs.
     * @param $title
     * @param $message
     * @return bool
     */
    public function log($title, $message)
    {
        if (!$this->_debug) {
            return true;
        }

        $time = $this->localeDate->date()->format('Y-m-d H:i:s');

        if (is_array($message) || is_object($message)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $message = print_r($message, true);
        }

        $line = "** {$time} ** {$this->_logType} ** {$title} **:\r\n{$message}\r\n";

        $logFile = $this->getLogFile();

        if ($logFile) {
            return error_log($line, 3, $logFile);
        }

        return false;
    }

    /**
     * Set the log type to save different type log to different log file
     * @return $this
     */
    public function setReturnLog()
    {
        $this->_logType = 'return';
        $this->_logFile = null;

        return $this;
    }

    /**
     * Set return log type, to save new return type log file
     * @return $this
     */
    public function setRequestLog()
    {
        $this->_logType = 'request';
        $this->_logFile = null;

        return $this;
    }

    /**
     * Set the log type to save different type log to different log file
     * @return $this
     */
    public function setNotifyLog()
    {
        $this->_logType = 'notify';
        $this->_logFile = null;
        return $this;
    }

    /**
     * Set the log type to save different type log to different log file
     * @return $this
     */
    public function setRefundLog()
    {
        $this->_logType = 'refund';
        $this->_logFile = null;
        return $this;
    }

    /**
     * Get the log file directory to save the payment method's logs.
     *
     * @return bool|null|string
     */
    public function getLogFile()
    {
        $logDir = $this->_getBaseDir();
        if (!$logDir) {
            return false;
        }

        $logDir = $logDir . DIRECTORY_SEPARATOR . $this->_logDir;

        if (!$this->_logFile) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (!file_exists($logDir)) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                if (!mkdir($logDir, 0777)) {
                    return false;
                }

                $this->createHtaccessFile($logDir);
                $this->createIndexFile($logDir);
            }

            $fileName =  $this->localeDate->date()->format('Ymd');
            if ($this->_logType) {
                $logFile = $logDir . DIRECTORY_SEPARATOR . $this->_logType.'_' . $fileName . '.log';
            } else {
                $logFile = $logDir . DIRECTORY_SEPARATOR . $fileName  . '.log';
            }

            $this->_logFile = $logFile;
        }

        return $this->_logFile;
    }

    /**
     * @param string $directoryName
     *
     * @return bool|string
     */
    public function createQrcodeDir($directoryName = 'qrcode')
    {
        $logDir = $this->_getBaseDir() . '/' . $this->_logDir . '/' . $directoryName;

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!file_exists($logDir)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (mkdir($logDir, 0777, true)) {
                $this->createHtaccessFile($logDir);
                $this->createIndexFile($logDir);
                return $logDir;
            }
        } else {
            return $logDir;
        }

        return false;
    }

    /**
     * Create .htaccess file and index.html to forbid visit log file through url
     * @param $path
     * @return bool|int
     */
    private function createHtaccessFile($path)
    {
        $htFile = $path . DIRECTORY_SEPARATOR . '.htaccess';

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!file_exists($htFile)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return file_put_contents($htFile, "Order deny,allow\r\nDeny from all");
        }

        return true;
    }

    /**
     * Create index.html to forbid visit log file through url
     *
     * @param $path
     * @return bool|int
     */
    private function createIndexFile($path)
    {
        $indexFile = $path . DIRECTORY_SEPARATOR . 'index.html';

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!file_exists($indexFile)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return file_put_contents($indexFile, "");
        }

        return true;
    }

    /**
     * Get the config value from the config table
     *
     * @param $key
     * @return string
     */
    public function getConfigValue($key): string
    {
        return (string) $this->scopeConfig->getValue(
            \Zhixing\UnionPayInstallmentsQrcode\Helper\Context::CONFIG_GROUP . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Build url
     *
     * @param $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = []): string
    {
        return $this->_getUrl($route, $params);
    }
}
