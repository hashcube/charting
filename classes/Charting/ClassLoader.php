<?php

/**
 * SPL class loader
 *
 * @author Paul Annesley
 * @modified to support namespaces
 * @package Charting
 * @license http://www.opensource.org/licenses/mit-license.php
 */
namespace Charting;

class ClassLoader
{
  const PACKAGE = 'Charting';

  private static $_path;

  /**
   * Registers Charting_ClassLoader as an SPL class loader.
   * Inserts self first, retains existing loaders and __autoload()
   *
   * @param string $path Path to Charting classes directory
   */
  public static function register($path)
  {
    self::$_path = $path;
    self::addPath($path);

    if ($loaders = spl_autoload_functions())
      array_map('spl_autoload_unregister', $loaders);
    else
      $loaders = function_exists('__autoload') ? array('__autoload') : array();

    $loaders []= array(__CLASS__, 'load');

    array_map('spl_autoload_register', $loaders);
  }

  /**
   * Attempts to load a Charting class file.
   *
   * @param string $class
   * @see http://php.net/manual/en/function.spl-autoload-register.php
   */
  public static function load($class)
  {
    //Note: '\' is double escaped
    $ns_string = self::PACKAGE.'\\';
    if (substr($class, 0, strlen($ns_string)) != $ns_string)
      return false;

    $path = sprintf(
      '%s/%s.php',
      self::$_path,
      str_replace('\\', '/', $class)
      );

    if (file_exists($path)) require_once($path);
  }

  /**
   * @param mixed $items Path or paths as string or array
   */
  public static function addPath($items)
  {
    $elements = explode(PATH_SEPARATOR, get_include_path());
    set_include_path(implode(
                       PATH_SEPARATOR,
                       array_unique(array_merge((array)$items, $elements))
                       ));
  }
}
?>