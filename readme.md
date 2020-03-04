# Yigim Laravel
---
#### Install

Laravel 5.5+
```console
composer require chameleon/yigimphp
```
### Usage
#### Laravel
```php
$client = Yigim::createClient([
    'msisdn' => '+994112223344',
    'name'   => 'Client'
]);
$invoice = Yigim::createInvoice([
    'client'    => $client->payload('reference'),
    'name'      => 'Payment Name',
    'amount'    => 100, //1azn
    'currency'  => 944,
    'reference' => 'yourUniquePaymentReference',
    'deadline'  => Carbon::now()->toDateString(),
    'status'    => 4,
    'option'    => [1, 2, 4]
]);
$invoice->payload('number');

//Hook

/**
 * @var \Illuminate\Http\Request $request
 */
$yigimHook = Yigim::handleHook(config('yigim.hook_key'), $request->query());
if ($yigimHook->type() === YigimHook::TYPE['INVOICE']) {
    $invoice = Yigim::getInvoice($yigimHook->reference());
    $invoiceNumber = $invoice->payload('number'));
    if ($invoiceNumber !== null) {
        switch ($yigimHook->action()) {
            case YigimHook::INVOICE_ACTION['PAID_FULL']:
                // pay full
            case YigimHook::INVOICE_ACTION['PAID_PART']:
                // pay part
            case YigimHook::INVOICE_ACTION['CANCEL']:
            case YigimHook::INVOICE_ACTION['DELETE']:
                // Cancel
        }
    }
}
```