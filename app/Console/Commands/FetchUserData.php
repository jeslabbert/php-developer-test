<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FetchUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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

            $bar = $this->output->createProgressBar(count($usersArray));
            $bar->start();
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
                $bar->advance();
            }
            $bar->finish();

        } catch(GuzzleHttp\Exception\GuzzleException $e) {
            // Handle exception
            $this->info('Processing failed');
        }
        $this->newLine();
        $this->info('Processing Completed');
        return 0;
    }
}
