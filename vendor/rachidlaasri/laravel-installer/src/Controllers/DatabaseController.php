<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;
use Request;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {

        $purchase_code = env('PURCHASE_CODE', 'default_value');
 	    $app_key = env('APP_KEY', 'default_value');
        $version = env('APP_VERSION', 'default_value');
        $resp_data = [];
        $errorMessage = "Something went wrong!";
        $server_name = Request::server("SERVER_NAME");
        // Verifying Purchase Code
        $resp_data["data"] = array(
            'site_name' => 'GoBiz',
            'currency' => 'INR',
            'timezone' => 'UTC',
            'paypal_mode' => 'live',
            'paypal_client_id' => 'YOUR_PAYPAL_CLIENT_ID',
            'paypal_secret' => 'YOUR_PAYPAL_SECRET',
            'razorpay_key' => 'YOUR_RAZORPAYID',
            'razorpay_secret' => 'YOUR_RAZORPAYID',
            'term' => 'yearly',
            'stripe_publishable_key' => 'YOUR_STRIPE_PUB_KEY',
            'stripe_secret' => 'YOUR_STRIPE_SECRET',
            'app_theme' => 'indigo',
            'primary_image' => '/frontend/assets/elements/yourimage.png',
            'secondary_image' => '/frontend/assets/elements/home.svg',
            'tax_type' => 'exclusive',
            'invoice_prefix' => 'INV-',
            'invoice_name' => 'TITLE',
            'invoice_email' => 'user@gmail.com',
            'invoice_phone' => '+1124569821',
            'invoice_address' => 'Address',
            'invoice_city' => 'City',
            'invoice_state' => 'State',
            'invoice_zipcode' => 'Pincode',
            'invoice_country' => 'United State',
            'tax_name' => 'TAX',
            'tax_value' => '18',
            'tax_number' => 'TAXNUMBER',
            'email_heading' => 'Thanks for using. This is an invoice for your recent purchase.',
            'email_footer' => 'If youre having trouble with the button above => please login into your web browser.',
            'invoice_footer' => 'Thank you very much for doing business with us. We look forward to working with you again!',
            'share_content' => 'Welcome to {business_name} => Visit my digital vCard or Watsapp Store by clicking {business_url}.',
            'bank_transfer' => 'BANK'
        );
        $resp_data['status'] = true;

        Artisan::call('migrate:reset', ['--force' => true]);

        if ($resp_data) {
            if ($resp_data['status'] == true) {
                $config_data = $resp_data['data'];

                $response = $this->databaseManager->migrateAndSeed();

                foreach ($config_data as $x=>$x_value) {
                    DB::table('config')->insert([
                        'config_key' => $x,
                        'config_value' => $x_value,
                    ]);
                }

                return redirect()->route('LaravelInstaller::final')->with(['message' => $response]);

            } else {
                $errorMessage = $resp_data['message'];
                return redirect()->route('LaravelInstaller::environmentClassic')->with([
                    'message' => $errorMessage,
                ]);
            }
        } else {
            return redirect()->route('LaravelInstaller::environmentClassic')->with([
                'message' => $errorMessage,
            ]);
        }

    }
}
