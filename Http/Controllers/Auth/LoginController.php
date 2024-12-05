<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\EditUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DomainsModel;
use App\Models\PagesModel;
use Illuminate\Support\Facades\Gate;

class LoginController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository,)
    {
        $this->userRepository = $userRepository;
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $token = Auth::user()->createToken('Personal Access Token')->accessToken;
            // $abilities = [
            //     [
            //         'action' => 'create',
            //         'subject' => 'order'
            //     ],
            //     [
            //         'action' => 'read',
            //         'subject' => 'order'
            //     ],
            //     [
            //         'action' => 'read',
            //         'subject' => 'dashboard'
            //     ]
            // ];
            $role = Auth::user()->role;
            if( $role =='User'){
                $abilities = [
                    [
                        'action' => 'read',
                        'subject' => 'order'
                    ],
                    
                ];
            }else{
                $abilities = [
                        [
                            'action' => 'read',
                            'subject' => 'dashboard'
                        ]
                    ];
                
            }
            // return response()->json(['access_token' => $token], 200);
            return response()->json([
                'token' => $token,
                'refresh_token' => $token,
                'abilities' => $abilities,
                'role' => $role,
                "token_type"=> "bearer",
                "name"=> "test name",
                'userId' => '4',
                'status'=> 200,
                'msg'=> 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function me()
    {
        $userInfo = User::where('id' ,auth()->user()->id )->first();
        return response()->json($userInfo);
    }

    public function user_listing(Request $request){
        if (auth()->user()->role == 'Admin') {
            $per_page = $request->query('per_page', 10);
            
            $query = User::with('domains', 'pages')->orderBy('id', 'desc');
            // Filter by active/deactive users
                if ($request->has('status')) {
                    $status = $request->query('status');
                    if ($status ==='Activated') {
                        // $query->whereNull('deleted_at');
                        $query->where('status', '1');
                    } elseif ($status ==='Deactivated') {
                        $query->where('status', '0');
                    }
                }
    
                // Search by user name
           // Search by name or email
            if ($request->has('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                }); 
            }
                    
                // Search by page name
                // if ($request->has('page_name')) {
                //     $pageName = $request->query('page_name');
                //     $query->whereHas('pages', function ($q) use ($pageName) {
                //         $q->where('name', 'LIKE', "%{$pageName}%");
                //     });
                // }
                
                // // Search by domain name
                // if ($request->has('domain_name')) {
                //     $domainName = $request->query('domain_name');
                //     $query->whereHas('domains', function ($q) use ($domainName) {
                //         $q->where('name', 'LIKE', "%{$domainName}%");
                //     });
                // }
                
                // Execute the query with pagination
                $usersList = $query->paginate($per_page);
                return response()->json([
                    'message' => 'success',
                    'status' => 200,
                    'data' => $usersList
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => 403
                ], 403);
            }
        }
    
    public function userStats(){
        $user = User::findOrFail(auth()->user()->id);

        // Get total number of pages
        $totalPages = $user->pages()->count();
    
        // Get total number of domains
        $totalDomains = $user->domains()->count();
    
        // Get total pages added in the last 7 days
        $pagesLast7Days = $user->pages()->where('created_at', '>=', now()->subDays(7))->count();
        $domainsLast7Days = $user->domains()->where('created_at', '>=', now()->subDays(7))->count();
    
        // Get total pages added in the last 30 days
        $pagesLast30Days = $user->pages()->where('created_at', '>=', now()->subDays(30))->count();
        $domainsLast30Days = $user->domains()->where('created_at', '>=', now()->subDays(30))->count();
    
        return response()->json([
            'status' => 200,
            'message' => 'User stats retrieved successfully',
            'data' => [
                'total_pages' => $totalPages,
                'total_domains' => $totalDomains,
                'pages_last_7_days' => $pagesLast7Days,
                'pages_last_30_days' => $pagesLast30Days,
                'domains_last_7_days' => $domainsLast7Days,
                'domains_last_30_days' => $domainsLast30Days,
            ]
        ]);
    }
    
    public function adminStats(){
        $totalUsers = User::count();
        // Get total number of pages
        $totalPages = PagesModel::count();

        // Get total number of domains
        $totalDomains = DomainsModel::count();

        // Get total pages added in the last 7 days
        $pagesLast7Days = PagesModel::where('created_at', '>=', now()->subDays(7))->count();
        $domainsLast7Days = DomainsModel::where('created_at', '>=', now()->subDays(7))->count();
        $usersLast7Days = User::where('created_at', '>=', now()->subDays(7))->count();
        $usersLast30Days = User::where('created_at', '>=', now()->subDays(30))->count();
        // Get total pages added in the last 30 days
        $pagesLast30Days = PagesModel::where('created_at', '>=', now()->subDays(30))->count();
        $domainsLast30Days = DomainsModel::where('created_at', '>=', now()->subDays(30))->count();

        return response()->json([
            'status' => 200,
            'message' => 'Admin stats retrieved successfully',
            'data' => [
                'total_users' => $totalUsers,
                'total_pages' => $totalPages,
                'total_domains' => $totalDomains,
                'pages_last_7_days' => $pagesLast7Days,
                'pages_last_30_days' => $pagesLast30Days,
                'domains_last_7_days' => $domainsLast7Days,
                'domains_last_30_days' => $domainsLast30Days,
                'users_last_30_days' => $usersLast30Days,
                'users_last_7_days' => $usersLast7Days,
            ]
        ]);
    }
    
    public function recentStats(){
        $recentUsers = User::orderBy('created_at', 'desc')->limit(10)->get();
        // Get total number of pages
        $recentPages = PagesModel::orderBy('created_at', 'desc')->limit(10)->get();

        // Get total number of domains
        $recentDomains = DomainsModel::orderBy('created_at', 'desc')->limit(10)->get();

        // // Get total pages added in the last 7 days
        $pagesLast7Days = PagesModel::where('created_at', '>=', now()->subDays(7))->count();
        $usersLast7Days = User::where('created_at', '>=', now()->subDays(7))->count();
        $usersLast30Days = User::where('created_at', '>=', now()->subDays(30))->count();
        $domainsLast7Days = DomainsModel::where('created_at', '>=', now()->subDays(7))->count();
        $totalUsers = User::count();
        $totalPages = PagesModel::count();
        $totalDomains = DomainsModel::count();
        $usersLast7Days = User::where('created_at', '>=', now()->subDays(7))->count();

        // // Get total pages added in the last 30 days
        $pagesLast30Days = PagesModel::where('created_at', '>=', now()->subDays(30))->count();
        $domainsLast30Days = DomainsModel::where('created_at', '>=', now()->subDays(30))->count();

        return response()->json([
            'status' => 200,
            'message' => 'Stats retrieved successfully',
            'data' => [
                'recent_users' => $recentUsers,
                'recent_pages' => $recentPages,
                'recent_domains' => $recentDomains,
                'last_7_days_pages' => $pagesLast7Days,
                'last_30_days_pages' => $pagesLast30Days,
                'last_30_days_domains' => $domainsLast30Days,
                'last_7_days_domains' => $domainsLast7Days,
                'users_last_30_days' => $usersLast30Days,
                'total_users' => $totalUsers,
                'total_pages' => $totalPages,
                'total_domains' => $totalDomains,
                'users_last_7_days' => $usersLast7Days,
            ]
        ]);
    }

    public function userAllData(Request $request){
        $usersList = User::where('uuid', $request->id)->with('domains', 'pages')->first();

        if ($usersList) {
            return response()->json([
                'message' => 'success',
                'status' => 200,
                'data' => $usersList
            ]);
        } else {
            return response()->json([
                'message' => 'Resource not found',
                'status' => 404,
            ], 404);
        }
    }

    public function softDelete($id)
    {
        $checkIfAdmin = 
        $user = User::with('pages', 'domains')->findOrFail($id);
        // Check if the user is an admin
        if ($user->role === 'Admin') {
            return response()->json([
                'status' => 403,
                'message' => 'Admin cannot be deleted',
            ], 403);
        }
        // Soft delete the user's pages and domains
        foreach ($user->pages as $page) {
            $page->delete();
        }

        foreach ($user->domains as $domain) {
            $domain->delete();
        }

        // Soft delete the user
        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'User and related data soft deleted successfully',
        ]);
    }

    public function editUser(EditUserRequest $request,$id){
        $user = User::findOrFail($id);

        // Authorize the request
        if (Gate::denies('update', $user)) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }
        $user->update($request->validated());
        return response()->json([
            'status' => 200,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
        
    }

}