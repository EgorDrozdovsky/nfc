<?php

namespace App\Http\Controllers\User;

use App\Plan;
use App\User;
use App\Theme;
use App\Medias;
use App\Gallery;
use App\Gateway;
use App\Payment;
use App\Service;
use App\Setting;
use App\Currency;
use Carbon\Carbon;
use App\Transaction;
use App\BusinessCard;
use App\BusinessHour;
use App\StoreProduct;
use App\BusinessField;
use Illuminate\Http\Request;
use Jorenvh\Share\ShareFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // All user cards
    public function cards()
    {
        $active_plan = Plan::where('plan_id', Auth::user()->plan_id)->first();
        $plan = User::where('user_id', Auth::user()->user_id)->first();

        if ($active_plan != null) {
            $business_cards = DB::table('business_cards')
                ->join('users', 'business_cards.user_id', '=', 'users.user_id')
                ->select('users.user_id', 'users.plan_validity', 'business_cards.*')
                ->where('business_cards.user_id', Auth::user()->user_id)->where('business_cards.status', 1)->orderBy('business_cards.id', 'desc')->get();
            $settings = Setting::where('status', 1)->first();

            return view('user.cards.cards', compact('business_cards', 'settings'));
        } else {
            if ($plan->billing_name != null) {
                return redirect()->route('user.plans');
            } else {
                return redirect()->route('user.billing');
            }
        }
    }

    public function plans()
    {
        $plans = DB::table('plans')->where('status', 1)->get();
        $config = DB::table('config')->get();
        $free_plan = Transaction::where('user_id', Auth::user()->id)->where('transaction_amount', '0')->count();
        $plan = User::where('user_id', Auth::user()->user_id)->first();
        $active_plan = json_decode($plan->plan_details);
        $settings = Setting::where('status', 1)->first();
        $currency = Currency::where('iso_code', $config[1]->config_value)->first();
        $remaining_days = 0;

        if (isset($active_plan)) {
            $plan_validity = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', Auth::user()->plan_validity);
            $current_date = Carbon::now();
            $remaining_days = $current_date->diffInDays($plan_validity, false);
        }

        if ($plan->billing_name != null) {
            return view('user.plans.plans', compact('plans', 'settings', 'currency', 'active_plan', 'remaining_days', 'config', 'free_plan'));
        } else {
            return redirect()->route('user.billing');
        }
    }

    // View Card Preview
    public function viewPreview(Request $request, $id)
    {
        $card_details = DB::table('business_cards')->where('card_id', $id)->where('status', 1)->first();

        if (isset($card_details)) {
            if ($card_details->card_type == "store") {
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

                    $plan_details = json_decode($business_card_details->plan_details, true);
                    $store_details = json_decode($business_card_details->description, true);

                    if ($store_details['whatsapp_no'] != null) {
                        $enquiry_button = $store_details['whatsapp_no'];
                    }

                    $whatsapp_msg = $store_details['whatsapp_msg'];
                    $currency = $store_details['currency'];

                    $url = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
                    $business_name = $card_details->title;
                    $profile = URL::to('/') . "/" . $business_card_details->profile;

                    $shareContent = $config[30]->config_value;
                    $shareContent = str_replace("{ business_name }", $business_name, $shareContent);
                    $shareContent = str_replace("{ business_url }", $url, $shareContent);
                    $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);

                    // If branding enabled, then show app name.

                    if ($plan_details['hide_branding'] == "1") {
                        $shareContent = str_replace("{ appName }", $business_name, $shareContent);
                    } else {
                        $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);
                    }

                    $url = urlencode($url);
                    $shareContent = urlencode($shareContent);


                    $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . $url;

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

                    $plan_details = json_decode($business_card_details->plan_details, true);

                    $url = URL::to('/') . "/" . strtolower(preg_replace('/\s+/', '-', $card_details->card_url));
                    $business_name = $card_details->title;
                    $profile = URL::to('/') . "/" . $business_card_details->profile;

                    $shareContent = $config[30]->config_value;
                    $shareContent = str_replace("{ business_name }", $business_name, $shareContent);
                    $shareContent = str_replace("{ business_url }", $url, $shareContent);
                    $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);

                    // If branding enabled, then show app name.

                    if ($plan_details['hide_branding'] == "1") {
                        $shareContent = str_replace("{ appName }", $business_name, $shareContent);
                    } else {
                        $shareContent = str_replace("{ appName }", $config[0]->config_value, $shareContent);
                    }

                    $url = urlencode($url);
                    $shareContent = urlencode($shareContent);


                    $qr_url = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . $url;

                    $shareComponent['facebook'] = "https://www.facebook.com/sharer/sharer.php?u=$url&quote=$shareContent";
                    $shareComponent['twitter'] = "https://twitter.com/intent/tweet?text=$shareContent";
                    $shareComponent['linkedin'] = "https://www.linkedin.com/shareArticle?mini=true&url=$url";
                    $shareComponent['telegram'] = "https://telegram.me/share/url?text=$shareContent&url=$url";
                    $shareComponent['whatsapp'] = "https://api.whatsapp.com/send/?phone&text=$shareContent";

                    return view('vcard.card-white', compact('card_details', 'plan_details', 'business_card_details', 'feature_details', 'service_details', 'galleries_details', 'payment_details', 'business_hours', 'settings', 'shareComponent', 'shareContent', 'config', 'enquiry_button'));
                } else {
                    alert()->error('Sorry, Please fill basic business details.');
                    return redirect()->route('user.company.details', $id);
                }
            }
        } else {
            http_response_code(404);
            return view('errors.404');
        }
    }

    // Create Card
    public function CreateCard()
    {
        $themes = Theme::where('theme_description', 'vCard')->where('status', 1)->first();
        $settings = Setting::where('status', 1)->first();
        $cards = BusinessCard::where('user_id', Auth::user()->user_id)->count();

        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        if ($cards < $plan_details->no_of_vcards) {
            return view('user.cards.create-card', compact('themes', 'settings', 'plan_details'));
        } else {
            alert()->error('Maximum card creation limit is exceeded, Please upgrade your plan.');
            return redirect()->route('user.cards');
        }
    }

    // Save card
    public function saveBusinessCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_id' => 'required',
            'card_color' => 'required',
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:' . env("SIZE_LIMIT") . '',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:' . env("SIZE_LIMIT") . '',
            'title' => 'required',
            'subtitle' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            alert()->error('Some fields missing or cover/logo size is large.');
            return back();
        }

        $cardId = uniqid();
        if ($request->link) {
            $personalized_link = $request->link;
        } else {
            $personalized_link = $cardId;
        }
        $cards = BusinessCard::where('user_id', Auth::user()->user_id)->count();
        $user_details = User::where('user_id', Auth::user()->user_id)->first();
        $plan_details = json_decode($user_details->plan_details, true);

        $logo = '/backend/img/vCards/' . 'IMG-' . uniqid() . '-' . str_replace(' ', '-', $request->logo->getClientOriginalName()) . '.' . $request->logo->extension();
        $cover = '/backend/img/vCards/' . 'IMG-' . uniqid() . '-' . str_replace(' ', '-', $request->cover->getClientOriginalName()) . '.' . $request->cover->extension();

        $request->logo->move(public_path('backend/img/vCards'), $logo);
        $request->cover->move(public_path('backend/img/vCards'), $cover);

        $card_url = strtolower(preg_replace('/\s+/', '-', $personalized_link));

        $current_card = BusinessCard::where('card_url', $card_url)->count();

        if ($current_card == 0) {
            // Checking, If the user plan allowed card creation is less than created card.
            if ($cards < $plan_details['no_of_vcards']) {
                try {
                    $card_id = $cardId;
                    $card = new BusinessCard();
                    $card->card_id = $card_id;
                    $card->user_id = Auth::user()->user_id;
                    $card->theme_id = $request->theme_id;
                    $card->theme_color = $request->card_color;
                    $card->card_lang = 'EN';
                    $card->cover = $cover;
                    $card->profile = $logo;
                    $card->card_url = $card_url;
                    $card->card_type = 'vcard';
                    $card->title = $request->title;
                    $card->sub_title = $request->subtitle;
                    $card->description = $request->description;
                    $card->save();

                    alert()->success('New Business Card Created Successfully!');
                    return redirect()->route('user.social.links', $card_id);
                } catch (\Exception $th) {
                    alert()->error('Sorry, personalized link was already registered.');
                    return redirect()->route('user.create.card');
                }
            } else {
                alert()->error('Maximum card creation limit is exceeded, Please upgrade your plan to add more card(s).');
                return redirect()->route('user.create.card');
            }
        } else {
            alert()->error('Sorry, personalized link was already registered.');
            return redirect()->route('user.create.card');
        }
    }

    // Social Links
    public function socialLinks()
    {
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $settings = Setting::where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        return view('user.cards.social-links', compact('plan_details', 'settings'));
    }

    // Save social links
    public function saveSocialLinks(Request $request, $id)
    {
        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card == null) {
            return view('errors.404');
        } else {
            if ($request->icon != null) {
                BusinessField::where('card_id', $id)->delete();
                $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                $plan_details = json_decode($plan->plan_details);

                if (count($request->icon) <= $plan_details->no_of_features) {
                    for ($i = 0; $i < count($request->icon); $i++) {
                        if (isset($request->icon[$i]) && isset($request->label[$i]) && isset($request->value[$i])) {
                            $field = new BusinessField();
                            $field->card_id = $id;
                            $field->type = $request->type[$i];
                            $field->icon = $request->icon[$i];
                            $field->label = $request->label[$i];
                            $field->content = $request->value[$i];
                            $field->position = $i + 1;
                            $field->save();
                        } else {
                            alert()->error('Atleast add one feature.');
                            return redirect()->route('user.social.links', $id);
                        }
                    }
                    alert()->success('features details updated');
                    return redirect()->route('user.payment.links', $id);
                } else {
                    alert()->error('You have reached plan features limited.');
                    return redirect()->route('user.social.links', $id);
                }
            } else {
                alert()->error('Atleast add one feature.');
                return redirect()->route('user.social.links', $id);
            }
        }
    }

    // Payment links
    public function paymentLinks()
    {
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $settings = Setting::where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);

        return view('user.cards.payment-links', compact('plan_details', 'settings'));
    }

    // Save payment links
    public function savePaymentLinks(Request $request, $id)
    {
        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card == null) {
            return view('errors.404');
        } else {
            if ($request->icon != null) {
                Payment::where('card_id', $id)->delete();
                $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                $plan_details = json_decode($plan->plan_details);

                if (count($request->icon) <= $plan_details->no_of_payments) {
                    for ($i = 0; $i < count($request->icon); $i++) {
                        if (isset($request->icon[$i]) && isset($request->label[$i]) && isset($request->value[$i])) {
                            $payment = new Payment();
                            $payment->card_id = $id;
                            $payment->type = $request->type[$i];
                            $payment->icon = $request->icon[$i];
                            $payment->label = $request->label[$i];
                            $payment->content = $request->value[$i];
                            $payment->position = $i + 1;
                            $payment->save();
                        } else {
                            alert()->error('Please fill all required fields.');
                            return redirect()->route('user.payment.links', $id);
                        }
                    }
                    alert()->success('Payment details updated');
                    return redirect()->route('user.services', $id);
                } else {
                    alert()->error('You have reached plan payments limited.');
                    return redirect()->route('user.payment.links', $id);
                }
            } else {
                alert()->success('Payment details updated');
                return redirect()->route('user.services', $id);
            }
        }
    }

    // Services
    public function services()
    {
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);
        $media = Medias::where('user_id', Auth::user()->user_id)->orderBy('id', 'desc')->get();
        $settings = Setting::where('status', 1)->first();

        return view('user.cards.services', compact('plan_details', 'settings', 'media'));
    }

    // Save services
    public function saveServices(Request $request, $id)
    {
        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card == null) {
            return view('errors.404');
        } else {
            $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
            $plan_details = json_decode($plan->plan_details);

            if($request->service_name != null){
    
            if (count($request->service_name) <= $plan_details->no_of_services) {
                    
                   for ($i = 0; $i < count($request->service_name); $i++) {
                    $service = new Service();
                    $service->card_id = $id;
                    $service->service_name = $request->service_name[$i];
                    $service->service_image =  $request->service_image[$i];
                    $service->service_description = $request->service_description[$i];
                    $service->enable_enquiry = $request->enquiry[$i];
                    $service->save();
                }
                alert()->success('Services details updated');
                return redirect()->route('user.galleries', $id); 
               
            } else {
                alert()->error('You have reached plan limit.');
                return redirect()->route('user.services', $id);
            }
          }else{
              //Skipping...
                 alert()->success('Services details updated');
                 return redirect()->route('user.galleries', $id); 
          }
        }
    }

    // Galleries
    public function galleries()
    {
        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $plan_details = json_decode($plan->plan_details);
        $media = Medias::where('user_id', Auth::user()->user_id)->orderBy('id', 'desc')->get();
        $settings = Setting::where('status', 1)->first();

        return view('user.cards.galleries', compact('plan_details', 'media', 'settings'));
    }

    // Save Gallery Images
    public function saveGalleries(Request $request, $id)
    {

        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card == null) {
            return view('errors.404');
        } else {
            $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
            $plan_details = json_decode($plan->plan_details);

            if($request->caption != null){
     
            if (count($request->caption) <= $plan_details->no_of_galleries) {
                for ($i = 0; $i < count($request->caption); $i++) {
                    $gallery = new Gallery();
                    $gallery->card_id = $id;
                    $gallery->caption = $request->caption[$i];
                    $gallery->gallery_image = $request->gallery_image[$i];
                    $gallery->save();
                }

                alert()->success('Gallery images updated');
                return redirect()->route('user.business.hours', $id);
            } else {
                alert()->error('You have reached plan limit.');
                return redirect()->route('user.galleries', $id);
            }
         }else{
               alert()->success('Gallery images updated');
               return redirect()->route('user.business.hours', $id);
         }
        }
    }

    // Business Hours
    public function businessHours()
    {
        $settings = Setting::where('status', 1)->first();

        return view('user.cards.business-hours', compact('settings'));
    }

    // Save business hours
    public function saveBusinessHours(Request $request, $id)
    {
        $business_card = BusinessCard::where('card_id', $id)->first();

        if ($business_card == null) {
            return view('errors.404');
        } else {
            if ($request->monday_closed == "on") {
                $monday = "Closed";
            } else {
                $monday = $request->monday_open . " - " . $request->monday_closing;
            }

            if ($request->tuesday_closed == "on") {
                $tuesday = "Closed";
            } else {
                $tuesday = $request->tuesday_open . " - " . $request->tuesday_closing;
            }

            if ($request->wednesday_closed == "on") {
                $wednesday = "Closed";
            } else {
                $wednesday = $request->wednesday_open . " - " . $request->wednesday_closing;
            }

            if ($request->thursday_closed == "on") {
                $thursday = "Closed";
            } else {
                $thursday = $request->thursday_open . " - " . $request->thursday_closing;
            }

            if ($request->friday_closed == "on") {
                $friday = "Closed";
            } else {
                $friday = $request->friday_open . " - " . $request->friday_closing;
            }

            if ($request->saturday_closed == "on") {
                $saturday = "Closed";
            } else {
                $saturday = $request->saturday_open . " - " . $request->saturday_closing;
            }

            if ($request->sunday_closed == "on") {
                $sunday = "Closed";
            } else {
                $sunday = $request->sunday_open . " - " . $request->sunday_closing;
            }

            if ($request->always_open == "on") {
                $always_open = "Opening";
            } else {
                $always_open = "Closed";
            }

            if ($request->is_display == "on") {
                $is_display = 0;
            } else {
                $is_display = 1;
            }

            $businessHours = new BusinessHour();
            $businessHours->card_id = $id;
            $businessHours->Monday = $monday;
            $businessHours->Tuesday = $tuesday;
            $businessHours->Wednesday = $wednesday;
            $businessHours->Thursday = $thursday;
            $businessHours->Friday = $friday;
            $businessHours->Saturday = $saturday;
            $businessHours->Sunday = $sunday;
            $businessHours->is_always_open = $always_open;
            $businessHours->is_display = $is_display;
            $businessHours->save();
            alert()->success('Your Business Card is Ready.');
            return redirect()->route('user.cards');
        }
    }

    // Skip business hours
    public function skipAndSave()
    {
        alert()->success('Your Business Card is Ready.');
        return redirect()->route('user.cards');
    }

    // Card Status Page
    public function cardStatus(Request $request, $id)
    {
        $businessCard = BusinessCard::where('card_id', $id)->first();

        if ($businessCard == null) {
            return view('errors.404');
        } else {
            $business_card = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->first();

            if ($business_card == null) {
                return view('errors.404');
            } else {
                if ($business_card->card_status == 'inactive') {
                    $plan = User::where('user_id', Auth::user()->user_id)->first();
                    $active_plan = json_decode($plan->plan_details);
                    $no_of_features = BusinessField::where('card_id', $id)->count();
                    $no_of_galleries = Gallery::where('card_id', $id)->count();
                    $no_of_payments = Payment::where('card_id', $id)->count();
                    $no_of_services = Service::where('card_id', $id)->count();
                    $no_of_products = StoreProduct::where('card_id', $id)->count();
                    if ($no_of_services <= $active_plan->no_of_services && $no_of_galleries <= $active_plan->no_of_galleries && $no_of_features <= $active_plan->no_of_features && $no_of_payments <= $active_plan->no_of_payments && $no_of_products <= $active_plan->no_of_services) {
                        $cards = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_status', 'activated')->count();

                        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                        $plan_details = json_decode($plan->plan_details);

                        if ($cards < $plan_details->no_of_vcards) {
                            BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->update([
                                'card_status' => 'activated',
                            ]);
                            alert()->success('Your Business Card Enabled');
                            return redirect()->route('user.cards');
                        } else {
                            alert()->error('Maximum card creation limit is exceeded, Please upgrade your plan.');
                            return redirect()->route('user.cards');
                        }
                    } else {
                        $cards = BusinessCard::where('user_id', Auth::user()->user_id)->where('card_status', 'activated')->count();

                        $plan = DB::table('users')->where('user_id', Auth::user()->user_id)->where('status', 1)->first();
                        $plan_details = json_decode($plan->plan_details);

                        if ($cards < $plan_details->no_of_vcards) {
                            return redirect()->route('user.edit.card', $id)->with('errors', 'Your plan was downgraded.');
                        } else {
                            alert()->error('Maximum card creation limit is exceeded, Please upgrade your plan.');
                            return redirect()->route('user.cards');
                        }
                    }
                } else {
                    BusinessCard::where('user_id', Auth::user()->user_id)->where('card_id', $id)->update([
                        'card_status' => 'inactive',
                    ]);
                    alert()->success('Your Business Card Disabled');
                    return redirect()->route('user.cards');
                }
            }
        }
    }

    // Checkout Page
    public function checkout(Request $request, $id)
    {
        $selected_plan = Plan::where('plan_id', $id)->where('status', 1)->first();
        if ($selected_plan == null) {
            alert()->error('Your current plan is not available. Choose another plan.');
            return redirect()->route('user.plans');
        } else {
            $config = DB::table('config')->get();
            $userData = User::where('id', Auth::user()->id)->first();

            if (isset($userData)) {
                if ($userData->billing_name == null || $userData->billing_address == null || $userData->billing_address == null || $userData->billing_city == null || $userData->billing_state == null || $userData->billing_zipcode == null || $userData->billing_country == null) {
                    return redirect()->route('user.billing')->with(['message' => "Please enter your billing details."]);
                }
            }

            if ($selected_plan == null) {
                return view('errors.404');
            } else {
                if ($selected_plan->plan_price == 0) {

                    $invoice_details = [];

                    $invoice_details['from_billing_name'] = $config[16]->config_value;
                    $invoice_details['from_billing_address'] = $config[19]->config_value;
                    $invoice_details['from_billing_city'] = $config[20]->config_value;
                    $invoice_details['from_billing_state'] = $config[21]->config_value;
                    $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
                    $invoice_details['from_billing_country'] = $config[23]->config_value;
                    $invoice_details['from_vat_number'] = $config[26]->config_value;
                    $invoice_details['from_billing_phone'] = $config[18]->config_value;
                    $invoice_details['from_billing_email'] = $config[17]->config_value;
                    $invoice_details['to_billing_name'] = $userData->billing_name;
                    $invoice_details['to_billing_address'] = $userData->billing_address;
                    $invoice_details['to_billing_city'] = $userData->billing_city;
                    $invoice_details['to_billing_state'] = $userData->billing_state;
                    $invoice_details['to_billing_zipcode'] = $userData->billing_zipcode;
                    $invoice_details['to_billing_country'] = $userData->billing_country;
                    $invoice_details['to_billing_phone'] = $userData->billing_phone;
                    $invoice_details['to_billing_email'] = $userData->billing_email;
                    $invoice_details['to_vat_number'] = $userData->vat_number;
                    $invoice_details['tax_name'] = $config[24]->config_value;
                    $invoice_details['tax_type'] = $config[14]->config_value;
                    $invoice_details['tax_value'] = $config[25]->config_value;
                    $invoice_details['invoice_amount'] = 0;
                    $invoice_details['subtotal'] = 0;
                    $invoice_details['tax_amount'] = 0;

                    $transaction = new Transaction();
                    $transaction->gobiz_transaction_id = uniqid();
                    $transaction->transaction_date = now();
                    $transaction->transaction_id = uniqid();
                    $transaction->user_id = Auth::user()->id;
                    $transaction->plan_id = $selected_plan->plan_id;
                    $transaction->desciption = $selected_plan->plan_name . " Plan";
                    $transaction->payment_gateway_name = "FREE";
                    $transaction->transaction_amount = $selected_plan->plan_price;
                    $transaction->transaction_currency = $config[1]->config_value;
                    $transaction->invoice_details = json_encode($invoice_details);
                    $transaction->payment_status = "SUCCESS";
                    $transaction->save();

                    $plan_validity = Carbon::now();
                    $plan_validity->addDays($selected_plan->validity);
                    User::where('user_id', Auth::user()->user_id)->update([
                        'plan_id' => $id,
                        'term' => "9999",
                        'plan_validity' => $plan_validity,
                        'plan_activation_date' => now(),
                        'plan_details' => $selected_plan,
                    ]);
                    // Making all cards inactive, For Plan change
                    BusinessCard::where('user_id', Auth::user()->user_id)->update([
                        'card_status' => 'inactive',
                    ]);
                    alert()->success("FREE Plan activated!");
                    return redirect()->back();
                } else {
                    $settings = Setting::where('status', 1)->first();
                    $config = DB::table('config')->get();
                    $currency = Currency::where('iso_code', $config[1]->config_value)->first();
                    $gateways = Gateway::where('is_status', 'enabled')->where('status', 1)->get();
                    $plan_price = $selected_plan->plan_price;
                    $tax = $config[25]->config_value;
                    $total = ((int)($plan_price) * (int)($tax) / 100) + (int)($plan_price);
                    return view('user.checkout.checkout', compact('settings', 'config', 'currency', 'selected_plan', 'gateways', 'total'));
                }
            }
        }
    }



    public function checkLink(Request $request)
    {
        $link = $request->link;
        $is_present = DB::table('business_cards')->where('card_url', $link)->count();
        $resp = [];
        $resp['status'] = 'failed';

        if ($is_present == 0) {
            $resp['status'] = 'success';
        } else {
            $resp['status'] = 'failed';
        }

        return response()->json($resp);
    }
}
