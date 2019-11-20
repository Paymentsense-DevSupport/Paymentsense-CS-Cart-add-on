<?php
/**
 * Copyright (C) 2019 Paymentsense Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package     CS-Cart Paymentsense add-on
 * @version     2.0
 * @author      Paymentsense
 * @copyright   2019 Paymentsense Ltd.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;

if (!function_exists('fn_paymentsense_uninstall')) {
    /**
     * Paymentsense uninstaller
     */
    function fn_paymentsense_uninstall()
    {
        $files = [
            '/app/addons/paymentsense',
            '/app/payments/paymentsense.php',
            '/app/payments/paymentsense_files',
            '/var/langs/en/addons/paymentsense.po',
            '/design/backend/templates/views/payments/components/cc_processors/paymentsense.tpl'
        ];
        $root_dir = Registry::get('config.dir.root');
        foreach ($files as $file) {
            if (!empty($file)) {
                fn_rm($root_dir . $file);
            }
        }
    }
}
