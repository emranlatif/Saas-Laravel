<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class ThriveCartController extends Controller
{
public function fetchOrderData(Request $request){
    // {"event": "order_refund", "target_url": "https://mysite.com/webhooks/123/", "trigger_fields": {"mode_int": 2, "refund": {"type": "upsell", "upsell_id": [1, 5, 18]}}}
    $data = $request->all();
    if (isset($data['event'])) {
        switch ($data['event']) {
            case 'order.success':
                $status = '1';
                $this->handleOrderCreate($data, $request->all(), $status);
                break;
            
            case 'order.refund':
                $status = '0';
                $this->handleOrderCreate($data, $request->all(), $status);
                break;

            case 'order.subscription_payment':
                $status = '1';
                $this->handleOrderCreate($data. $request->all(), $status);
                break;

            case 'order.subscription_cancelled':
                $status = '0';
                $this->handleOrderCreate($data. $request->all(), $status);
                break;

            case 'order.subscription_paused':
                $status = '0';
                $this->handleOrderCreate($data. $request->all(), $status);
                break;

            case 'order.subscription_resumed':
                $status = '1';
                $this->handleOrderCreate($data. $request->all(), $status);
                break;

            case 'order.rebill_failed':
                $status = '0';
                $this->handleOrderCreate($data. $request->all(),$status);
                break;

            default:
                $this->dummy($request->all());
                break;
        }
    }
    if(!$data){
         return response()->json(['message' => 'Not data Passed'], 200);
    }
    return response()->json(['message' => 'success'], 200);
    }

    private function handleOrderCreate($data, $request, $status){
        $response = json_encode($request, true);
        $customer = $data['customer'] ?? null;
        $productId = $data['base_product'];
        if(isset($$productId) && $productId !=33){
            return;
        }
        if ($customer) {
            $customerName = $customer['name'] ?? 'Unknown';
            $customerEmail = $customer['email'];
            $plainPassword = Str::random(8);

            // Check if a user with the same email already exists
            $existingUser = User::where('email', $customerEmail)->first();

            if ($existingUser) {
                // Update the status to 0 for the existing user
                $existingUser->status ='0';
                $existingUser->updated_at = now(); // Update the timestamp
                $existingUser->save();
            } else {
                // Create a new user with status 1
                $saveUser = [
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'password' => Hash::make($plainPassword),
                    'role' => 'User',
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                try {
                    User::create($saveUser);

                    ///Send email to the customer with the new account password
                    \Mail::raw("Your account has been created. Your password is: $plainPassword ,  Response: $response", function ($message) use ($customerEmail) {
                        $message->to($customerEmail)
                                ->subject('Your New Account Password');
                    });
                } catch (\Exception $e) {
                    \Log::error('Error creating user: ' . $e->getMessage());
                }
            }
        }
    }


    private function dummy($data){
            $customerName = 'dummy name';
            // $customerEmail = $customer['customer_email'] ?? 'Unknown';
            $customerEmail = 'shoaibranjha6@gmail.com'; 
            $customerAddress = 'dummy address';
            // $data = [
            //     'name' => $customerName,
            //     'email' => $customerEmail,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ];
            $dataDefault = json_encode($data, true);
            // try {
            //     User::create($data);
            // } catch (\Exception $e) {
            //     // Handle exception if needed, e.g., log the error or notify the user
            //     \Log::error('Error creating user: ' . $e->getMessage());
            // }
            \Mail::raw("default Order Data:  $dataDefault", function ($message) use ($customerEmail) {
                $message->to($customerEmail)
                    ->subject('Your New Account Password');
            });
    }

    
}
