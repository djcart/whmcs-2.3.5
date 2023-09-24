<?php
namespace WHMCS\Module\Addon\domenytv\Admin\Controllers\Ssl;

use WHMCS\Module\Common\domenytv\Admin\Controllers\Controller;
use WHMCS\Database\Capsule;

class SslController extends Controller
{
    public function updateSslOrdersStatuses()
    {

        $orders = Capsule::table('dtv_ssl_orders')
            ->orderBy('add_date', 'desc')
            ->get();

        foreach ($orders as $order) {
            $this->updateSslOrderStatus($order->order_id);
        }
    }

    public function updateSslOrderStatus($orderId)
    {
        $status = $this->getSslOrderStatus($orderId);

        if ($status !== false) {
            Capsule::table('dtv_ssl_orders')
                ->where('order_id', $orderId)
                ->update(
                    array(
                        'status' => $status,
                        'update_date' => date('Y-m-d H:i:s'),
                    )
                );

            return $status;
        }

        return null;
    }

    public function getSslOrderStatus($orderId)
    {
        $data['order_id'] = $orderId;

        $response = $this->api->sslCheckOrder($data);

        if ($response['result'] == 1000) {

            switch ($response['status1']) {
                case 'PENDING':
                    $status = 0;
                    break;
                case 'CANCELLED':
                    $status = 2;
                    break;
                case 'COMPLETE':
                    $status = 1;
                    break;
                case 'NO_INFO':
                    $status = 3;
                    break;
                default:
                    $status = 666;
            }

            return $status;
        }

        return false;
    }

    public function install() {

        if (!Capsule::schema()->hasTable('dtv_ssl_orders'))
        {
            try {
                Capsule::schema()
                    ->create(
                        'dtv_ssl_orders',
                        function ($table) {
                            $table->increments('id');
                            $table->integer('order_id');
                            $table->string('domain');
                            $table->string('product');
                            $table->integer('period');
                            $table->string('first_name');
                            $table->string('last_name');
                            $table->string('phone');
                            $table->string('email');
                            $table->string('admin_email');
                            $table->string('company');
                            $table->string('address');
                            $table->string('city');
                            $table->string('district');
                            $table->string('zip');
                            $table->string('nip');
                            $table->string('country');
                            $table->tinyInteger('status');
                            $table->dateTime('add_date');
                            $table->dateTime('update_date');
                        }
                    );
                return [
                    'status' => 'success',
                    'description' => 'Moduł został poprawnie zainstalowany',
                ];
            } catch (\Exception $e) {
                return [
                    'status' => "error",
                    'description' => 'Nie udało się zainstalować modułu ' . $e->getMessage(),
                ];
            }


        }

        return [
            'status' => 'success',
            'description' => 'Moduł został poprawnie zainstalowany',
        ];
    }
}