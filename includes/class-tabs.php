<?php
namespace WPTP;

/**
 * Class Tabs
 *
 * @since 1.0.0
 */
class Tabs {
    private static $data = array();
    private static $tab = array();

    public static function set($data) {
        self::$data = $data;
    }

    public static function get() {
        return self::$data;
    }

    /**
     * @param $tab
     */
    public static function setCurrentTab($tab) {
        self::$tab = $tab;
    }

    public static function getCurrentTab() {
        return self::$tab;
    }
}
