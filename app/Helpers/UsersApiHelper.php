<?php
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

if (! function_exists('fetchUsers')) {
    function fetchUsers()
    {
        try {
            //Set my base API uri up for use in my calls
            $client = new Client(['base_uri' => 'https://reqres.in/']);

            //Get the response and convert it to a useable format.
            $firstResponse = $client->request('GET', '/api/users');
            $firstResults = $firstResponse->getBody();
            $firstResults = json_decode($firstResponse->getBody());

            //After doing some checks on this API I found I can easily force all results through in one call if needed, however, I can also create a for loop to run through all the paginated results. For now I will just get all through in one response to begin with.

            //Get the total results as well as the other pieces of information that will help me build my for loop in the future if required
            $total = $firstResults->total;
            $perPage = $firstResults->per_page;
            $totalPages = $firstResults->total_pages;

            //Call to get all users in one page, it does however return all users including the users we have already fetched. Since it was only 6, I didnt stress too much but a fix would be the for loop based on the default per page count
            $totalResponse = $client->request('GET', '/api/users', ['query' => ['page' => '1', "per_page" => $total]]);
            $totalResults = $totalResponse->getBody();
            $totalResults = json_decode($totalResponse->getBody());

            //Get our users data from the overall call.
            $usersArray = $totalResults->data;

            foreach ($usersArray as $user) {


                //I add a quick check to see if the user already exists
                $currentUser = \App\Models\User::where('email', $user->email)->first();

                if($currentUser) {
                    //Update the current user
                    $currentUser->update([
                        'email'=>$user->email,
                        'first_name'=>$user->first_name,
                        'last_name'=>$user->last_name,
                        'avatar'=>$user->avatar
                    ]);
                } else {
                    \App\Models\User::create([
                        'email'=>$user->email,
                        'first_name'=>$user->first_name,
                        'last_name'=>$user->last_name,
                        'avatar'=>$user->avatar,
                        'password'=>Hash::make(Str::random(10))
                    ]);
                }

            }
            return 'Success';

        } catch(GuzzleHttp\Exception\GuzzleException $e) {
            // Handle exception
            return $e->getMessage();
        }
    }
}
