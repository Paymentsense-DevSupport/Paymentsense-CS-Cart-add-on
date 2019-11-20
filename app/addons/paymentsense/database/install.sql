DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = 'paymentsense.php'));
DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = 'paymentsense.php');
DELETE FROM ?:payment_processors WHERE processor_script = 'paymentsense.php';
INSERT INTO ?:payment_processors (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('Paymentsense', 'paymentsense.php', 'views/orders/components/payments/cc_outside.tpl', 'paymentsense.tpl', 'N', 'P');
