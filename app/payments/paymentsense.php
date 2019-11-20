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

require_once __DIR__ . '/paymentsense_files/PaymentsenseIso4217.php';
require_once __DIR__ . '/paymentsense_files/PaymentsensePaymentMethod.php';

$order_id = null;
if (!empty($_REQUEST['OrderID'])) {
    $order_id = strpos($_REQUEST['OrderID'], '_')
        ? substr($_REQUEST['OrderID'], 0, strpos($_REQUEST['OrderID'], '_'))
        : $_REQUEST['OrderID'];
}

if (!defined('BOOTSTRAP')) {
    if (!is_null($order_id)) {
        require_once './init_payment.php';

        $order_info     = fn_get_order_info($order_id);
        $payment_method = new PaymentsensePaymentMethod($order_info['payment_method']);

        if ($payment_method->isHashDigestValid()) {
            fn_order_placement_routines('route', $order_id, false);
        } else {
            die('Access denied');
        }
    } else {
        die('Access denied');
    }
}

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'process') {
        if (!is_null($order_id)) {

            $order_info     = fn_get_order_info($order_id);
            $payment_method = new PaymentsensePaymentMethod($order_info['payment_method']);

            if ($payment_method->isHashDigestValid()) {
                if (fn_check_payment_script(basename(__FILE__), $order_id)) {
                    fn_finish_payment(
                        $order_id,
                        $payment_method->getPaymentResponse()
                    );
                    $payment_method->setSuccessResponse();
                } else {
                    $payment_method->setErrorResponse('Invalid Payment Script');
                }
            } else {
                $payment_method->setErrorResponse('Invalid Hash Digest');
            }
            $payment_method->outputResponse();
        }
    }
} else {
    /** @var array $processor_data */
    $payment_method = new PaymentsensePaymentMethod($processor_data);
    fn_create_payment_form(
        $payment_method->getPaymentFormUrl(),
        $payment_method->buildHpfFields($order_info),
        $processor_data['processor'],
        false
    );
}

exit;
