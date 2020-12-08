<?php


namespace rapidPHP\modules\application\classier;

use Exception;
use rapidPHP\modules\application\config\ApplicationConfig;
use rapidPHP\modules\application\wrapper\ConfigWrapper;
use rapidPHP\modules\common\classier\AR;
use rapidPHP\modules\common\config\VarConfig;
use rapidPHP\modules\exception\classier\RuntimeException;
use rapidPHP\modules\logger\classier\Logger;
use rapidPHP\modules\reflection\classier\Utils;
use Spyc;


abstract class Application
{

    /**
     * logger error
     */
    const LOGGER_ERROR = 'error';

    /**
     * logger warning
     */
    const LOGGER_WARNING = 'warning';

    /**
     * logger debug
     */
    const LOGGER_DEBUG = 'debug';

    /**
     * logger access
     */
    const LOGGER_ACCESS = 'access';

    /**
     * 基本配置
     * @var ConfigWrapper
     */
    private $config;

    /**
     * 基本配置 (原始数据)
     * @var array
     */
    private $rawConfig = [];

    /**
     * @var Application
     */
    private static $instance;

    /**
     * @return Application
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Application constructor.
     * @param string $appFile
     * @throws RuntimeException
     */
    public function __construct(?string $appFile = null)
    {
        try {
            $this->setConfig(ApplicationConfig::getDefaultConfig());

            if (is_null($appFile)) {
                $appFile = PATH_APP . 'application.yaml';
            }

            if (is_file($appFile)) {
                $this->setConfig(Spyc::YAMLLoad($appFile));
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        self::$instance = $this;
    }


    /**
     * 设置全局config
     * 请在run之前调用，否则无法调用
     * @param array $config
     */
    public function setConfig(array $config)
    {
        AR::getInstance()->merge($this->rawConfig, $config);
    }

    /**
     * @return array|ConfigWrapper
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function getRawConfig(?string $key = null): ?array
    {
        if ($key) return isset($this->rawConfig[$key]) ? $this->rawConfig[$key] : null;

        return $this->rawConfig;
    }

    /**
     * 解析config
     * @throws Exception
     */
    public function onParseConfig()
    {
        VarConfig::parseVarByArray($this->rawConfig);

        $this->config = Utils::getInstance()->toObject(ConfigWrapper::class, $this->rawConfig);
    }

    /**
     * 运行
     * @throws RuntimeException
     */
    public function run()
    {
        try {
            $this->onParseConfig();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 获取logger
     * @param string $name
     * @return Logger|null
     * @throws Exception
     */
    public function logger($name = self::LOGGER_WARNING)
    {
        $config = $this->getConfig()->getLogConfig($name);

        if (empty($config)) return null;

        return Logger::getLogger($config);
    }
}