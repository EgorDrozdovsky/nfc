<?php

namespace App\Http\Controllers;

use App\Setting;
use Carbon\Carbon;
use App\BusinessCard;
use App\BusinessField;
use Illuminate\Http\Request;
use Jorenvh\Share\ShareFacade;
use JeroenDesloovere\VCard\VCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    // View Card Profile
    public function profile(Request $request, $id)
    {
        $card_details = DB::table('business_cards')->where('card_url', $id)->orWhere('card_id', $id)->where('card_status', 'activated')->first();
        $currentUser = 0;

        if(isset($card_details)){
            $currentUser = DB::table('users')->where('user_id', $card_details->user_id)->where('status', 1)->whereDate('plan_validity', '>=', Carbon::now())->count();
        }

        if ($currentUser == 1) {
            if (isset($card_details)) {
                if($card_details->card_type == "store") {
                    $enquiry_button = '#';

                    $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $card_details->card_id)
                        ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                        ->select('business_cards.*', 'users.plan_details')
                        ->first();

                    if ($business_card_details) {

                        $products = DB::table('store_products')->where('card_id', $card_details->card_id)->orderBy('id', 'desc')->get();

                        $settings = Setting::where('status', 1)->first();
                        $config = DB::table('config')->get();

                        App::setLocale($business_card_details->card_lang);
                        session()->put('locale', $business_card_details->card_lang);

                        SEOTools::setTitle($business_card_details->title);
                        SEOTools::setDescription($business_card_details->sub_title);

                        SEOMeta::setTitle($business_card_details->title);
                        SEOMeta::setDescription($business_card_details->sub_title);
                        SEOMeta::addMeta('article:section', $business_card_details->title, 'property');
                        SEOMeta::addKeyword(["'".$business_card_details->title."'", "'".$business_card_details->title." vcard online'"]);

                        OpenGraph::setTitle($business_card_details->sub_title);
                        OpenGraph::setDescription($business_card_details->sub_title);
                        OpenGraph::setUrl(URL::to('/') . '/'.$business_card_details->card_url);
                        OpenGraph::addImage([URL::to('/') . $business_card_details->profile, 'size' => 300]);

                        JsonLd::setTitle($business_card_details->title);
                        JsonLd::setDescription($business_card_details->sub_title);
                        JsonLd::addImage(URL::to('/') . $business_card_details->profile);

                        $plan_details = json_decode($business_card_details->plan_details, true);
                        $store_details = json_decode($business_card_details->description, true);

                        if ($store_details['whatsapp_no'] != null) {
                            $enquiry_button = $store_details['whatsapp_no'];
                        }

                        $whatsapp_msg = $store_details['whatsapp_msg'];
                        $whatsapp_msg = str_replace('"', '', $whatsapp_msg);
                        $whatsapp_msg = str_replace("'", '', $whatsapp_msg);
                        $whatsapp_msg = str_replace("\n", 'NL', $whatsapp_msg);
                        $whatsapp_msg = str_replace("\r", 'NL', $whatsapp_msg);
                        $currency = $store_details['currency'];

                        $url = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
                        $business_name = $card_details->title;
                        $profile = URL::to('/') . "/" . $business_card_details->cover;

                        $shareContent = $config[30]->config_value;
                        $shareContent = str_replace("{ business_name }",$business_name,$shareContent);
                        $shareContent = str_replace("{ business_url }",$url,$shareContent);
                        $shareContent = str_replace("{ appName }", $config[0]->config_value ,$shareContent);

                        // If branding enabled, then show app name.

                        if($plan_details['hide_branding'] == "1") {
                            $shareContent = str_replace("{ appName }", $business_name ,$shareContent);
                        } else {
                            $shareContent = str_replace("{ appName }", $config[0]->config_value ,$shareContent);
                        }

                        $url = urlencode($url);
                        $shareContent = urlencode($shareContent);


                        $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=".$url;

                        $shareComponent['facebook'] = "https://www.facebook.com/sharer/sharer.php?u=$url&quote=$shareContent";
                        $shareComponent['twitter'] = "https://twitter.com/intent/tweet?text=$shareContent";
                        $shareComponent['linkedin'] = "https://www.linkedin.com/shareArticle?mini=true&url=$url";
                        $shareComponent['telegram'] = "https://telegram.me/share/url?text=$shareContent&url=$url";
                        $shareComponent['whatsapp'] = "https://api.whatsapp.com/send/?phone&text=$shareContent";

                        return view('vcard.store', compact('card_details', 'plan_details', 'store_details', 'business_card_details', 'products', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button', 'whatsapp_msg', 'currency'));
                    } else {
                        alert()->error('Sorry, Please fill basic business details.');
                        return redirect()->route('user.edit.card', $id);
                    }
                } else {
                    $enquiry_button = null;
                    $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $card_details->card_id)
                        ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                        ->select('business_cards.*', 'users.plan_details')
                        ->first();

                    if ($business_card_details) {

                        $feature_details = DB::table('business_fields')->where('card_id', $card_details->card_id)->get();
                        $service_details = DB::table('services')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $galleries_details = DB::table('galleries')->where('card_id', $card_details->card_id)->orderBy('id', 'asc')->get();
                        $payment_details = DB::table('payments')->where('card_id', $card_details->card_id)->get();
                        $business_hours = DB::table('business_hours')->where('card_id', $card_details->card_id)->first();
                        $make_enquiry = DB::table('business_fields')->where('card_id', $card_details->card_id)->where('type', 'wa')->first();

                        if ($make_enquiry != null) {
                            $enquiry_button = $make_enquiry->content;
                        }

                        $settings = Setting::where('status', 1)->first();
                        $config = DB::table('config')->get();

                        App::setLocale($business_card_details->card_lang);
                        session()->put('locale', $business_card_details->card_lang);

                        SEOTools::setTitle($business_card_details->title);
                        SEOTools::setDescription($business_card_details->sub_title);

                        SEOMeta::setTitle($business_card_details->title);
                        SEOMeta::setDescription($business_card_details->sub_title);
                        SEOMeta::addMeta('article:section', $business_card_details->title, 'property');
                        SEOMeta::addKeyword(["'".$business_card_details->title."'", "'".$business_card_details->title." vcard online'"]);

                        OpenGraph::setTitle($business_card_details->sub_title);
                        OpenGraph::setDescription($business_card_details->sub_title);
                        OpenGraph::setUrl(URL::to('/') . '/'.$business_card_details->card_url);
                        OpenGraph::addImage([URL::to('/') . $business_card_details->profile, 'size' => 300]);

                        JsonLd::setTitle($business_card_details->title);
                        JsonLd::setDescription($business_card_details->sub_title);
                        JsonLd::addImage(URL::to('/') . $business_card_details->profile);

                        $plan_details = json_decode($business_card_details->plan_details, true);

                        $url = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
                        $business_name = $card_details->title;
                        $profile = URL::to('/') . "/" . $business_card_details->cover;

                        $shareContent = $config[30]->config_value;
                        $shareContent = str_replace("{ business_name }",$business_name,$shareContent);
                        $shareContent = str_replace("{ business_url }",$url,$shareContent);

                        // If branding enabled, then show app name.

                        if($plan_details['hide_branding'] == "1") {
                            $shareContent = str_replace("{ appName }", $business_name ,$shareContent);
                        } else {
                            $shareContent = str_replace("{ appName }", $config[0]->config_value ,$shareContent);
                        }

                        $url = urlencode($url);
                        $shareContent = urlencode($shareContent);


                        $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=".$url;

                        $shareComponent['facebook'] = "https://www.facebook.com/sharer/sharer.php?u=$url&quote=$shareContent";
                        $shareComponent['twitter'] = "https://twitter.com/intent/tweet?text=$shareContent";
                        $shareComponent['linkedin'] = "https://www.linkedin.com/shareArticle?mini=true&url=$url";
                        $shareComponent['telegram'] = "https://telegram.me/share/url?text=$shareContent&url=$url";
                        $shareComponent['whatsapp'] = "https://api.whatsapp.com/send/?phone&text=$shareContent";

                        return view('vcard.card-white', compact('card_details', 'plan_details', 'business_card_details', 'feature_details', 'service_details', 'galleries_details', 'payment_details', 'business_hours', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button'));
                    } else {
                        alert()->error('Sorry, Please fill basic business details.');
                        return redirect()->route('user.edit.card', $id);
                    }
                }
            } else {
                http_response_code(404);
                return view('errors.404');
            }
        } else {
            return view('errors.404');
        }
    }

    public function downloadVcard(Request $request, $id)
    {
        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card == null) {
            return view('errors.404');
        } else {
            $business_card_details = DB::table('business_cards')->where('business_cards.card_id', $id)
                ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                ->select('business_cards.*')
                ->first();
            $features = BusinessField::where('card_id', $id)->get();

            $vcard_url = URL::to('/') . "/" . $business_card_details->card_id;

            // define vcard
            $vcard = new VCard();

            // define variables
            $lastname = '';
            $firstname = $business_card_details->title;
            $additional = '';
            $prefix = '';
            $suffix = '';
            $email = '';
            $tel = '';
            $whatsapp = '';


            // add personal data
            $vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);

            foreach ($features as $key => $value)
            {
                if($value->type == "email") {
                    $vcard->addEmail($value->content);
                }
                if($value->type == "tel") {
                    $vcard->addPhoneNumber($value->content, 'WORK');
                }
                if($value->type == "wa") {
                    $vcard->addPhoneNumber($value->content, 'WHATSAPP');
                }
                if($value->type == "url") {
                    $vcard->addURL($value->content);
                }
                if($value->type == "address") {
                    $vcard->addAddress($value->content);
                }
            }

            if ($business_card_details->profile == null) {
                $image = "";
            } else {
                $image = str_replace(' ','%20',public_path($business_card_details->profile));
            }

            // add work data
            $vcard->addJobtitle($business_card_details->sub_title);
            $vcard->addPhoto($image);
            $vcard->addURL($vcard_url);

            return Response::make($vcard->getOutput(),200,$vcard->getHeaders(true));

        }
    }
}
