--TEST--
PEAR_Config->readFTPConfigFile() (failure)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (!(include_once 'Net/FTP.php') || !(include_once 'PEAR/FTP.php')) {
    die('skip requires PEAR_RemoteInstall to work');
}
?>
--FILE--
<?php
error_reporting(E_ALL);
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
require_once 'PEAR/Command/Remoteinstall-init.php';
$config = &new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'pear.ini');
ini_set('include_path', './#####');
$config->readFTPConfigFile('ftp://example.com');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Net_FTP must be installed to use remote config'),
), 'no net_ftp');

include_once dirname(__FILE__) . '/test_readFTPConfigFile/FTP.php.inc';
ini_restore('include_path');
$ftp = &Net_FTP::singleton();
$ftp->setConnectError('connect error');
$e = $config->readFTPConfigFile('ftp://example.com');
$phpunit->assertIsa('PEAR_Error', $e, 'ftp://example.com');
$phpunit->assertEquals('No FTP file path to remote config specified', $e->getMessage(),
    'message ftp://example.com');
$e = $config->readFTPConfigFile('ftp://');
$phpunit->assertIsa('PEAR_Error', $e, 'ftp://');
$phpunit->assertEquals('No FTP Host specified', $e->getMessage(),
    'message ftp://');
$e = $config->readFTPConfigFile('ftp://example.com/config.ini');
$phpunit->assertIsa('PEAR_Error', $e, 'ftp://example.com/config.ini connect error');
$phpunit->assertEquals('connect error', $e->getMessage(),
    'message ftp://example.com/config.ini connect error');

$ftp->setLoginError('login error');
$ftp->setConnectError(false);
$e = $config->readFTPConfigFile('ftp://example.com/config.ini');
$phpunit->assertIsa('PEAR_Error', $e, 'ftp://example.com/config.ini login error');
$phpunit->assertEquals('login error', $e->getMessage(),
    'message ftp://example.com/config.ini login error');

$ftp->setLoginError(false);
$ftp->setConnectError(false);
$ftp->setCdError(array('/' => 'cd error'));
$e = $config->readFTPConfigFile('ftp://example.com/config.ini');
$phpunit->assertIsa('PEAR_Error', $e, 'ftp://example.com/config.ini cd error');
$phpunit->assertEquals('cd error', $e->getMessage(),
    'message ftp://example.com/config.ini cd error');

$ftp->setLoginError(false);
$ftp->setConnectError(false);
$ftp->setCdError(false);
$e = $config->readFTPConfigFile('ftp://example.com/config.ini');
$phpunit->assertIsa('PEAR_Error', $e, 'ftp://example.com/config.ini file not found');
$phpunit->assertEquals('File \'config.ini\' could not be downloaded to \'local\'.', $e->getMessage(),
    'message ftp://example.com/config.ini file not found');

$ftp->addRemoteFile('config.ini', dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'test_readFTPConfigFile' . DIRECTORY_SEPARATOR . 'novars.ini');
$e = $config->readFTPConfigFile('ftp://example.com/config.ini');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'ERROR: Ftp configuration file must set all directory configuration variables.  These variables were not set: "php_dir", "ext_dir", "doc_dir", "bin_dir", "data_dir", "test_dir", "cache_dir"')
), 'last failure');
$phpunit->assertIsa('PEAR_Error', $e, 'ftp://example.com/config.ini no dir vars');
echo 'tests done';
?>
--EXPECT--
tests done
