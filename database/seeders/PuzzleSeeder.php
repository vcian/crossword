<?php

namespace Database\Seeders;

use App\Models\Puzzle;
use App\Models\Clue;
use App\Services\CrosswordGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PuzzleSeeder extends Seeder
{
    private array $qaPool = [
        // Authentication & Authorization
        ["What is Laravel's authentication system called?", "Sanctum"],
        ["Which facade is used for authentication?", "Auth"],
        ["What middleware is used to restrict guest users?", "Guest"],
        ["Which class is used to hash passwords?", "Bcrypt"],
        ["Which method logs out a user?", "Logout"],
        ["What is used to handle authorization?", "Gate"],
        ["Which middleware ensures a user is logged in?", "Auth"],
        ["Which method checks if a user is authenticated?", "Check"],
        ["What is the default authentication guard?", "Web"],
        ["Which method retrieves the logged-in user?", "User"],
    
        // Routing & Middleware
        ["Which file contains web routes?", "Web"],
        ["Which command clears route cache?", "Routeclear"],
        ["What is used to protect routes?", "Auth"],
        ["Which file contains API routes?", "Api"],
        ["Which command lists all routes?", "Routelist"],
        ["What function redirects a route?", "Redirect"],
        ["How do you define a GET route?", "Get"],
        ["What middleware is used for API rate limiting?", "Throttle"],
        ["What groups multiple middleware together?", "Group"],
        ["Which directive generates a URL?", "Url"],
    
        // Database & Eloquent
        ["What is Laravel's ORM called?", "Eloquent"],
        ["What is Laravel's default database?", "Mysql"],
        ["Which command creates a migration?", "Migrate"],
        ["Which method inserts a record?", "Create"],
        ["Which command seeds the database?", "Dbseed"],
        ["Which method retrieves all records?", "Get"],
        ["What is the default primary key field?", "Id"],
        ["Which method updates records?", "Update"],
        ["Which command rolls back migrations?", "Rollback"],
        ["Which class defines a table structure?", "Schema"],
    
        // Blade & Views
        ["What is Laravel’s template engine?", "Blade"],
        ["Which directive creates a loop?", "Foreach"],
        ["Which directive defines a condition?", "If"],
        ["Which directive includes a file?", "Include"],
        ["Which directive extends a layout?", "Extends"],
        ["Which directive escapes HTML?", "Escape"],
        ["Which directive defines a section?", "Section"],
        ["Which directive protects against CSRF?", "Csrf"],
        ["Which directive ends a section?", "Endsection"],
        ["Which directive retrieves old input?", "Old"],
    
        // Artisan Commands
        ["What is Laravel's CLI tool called?", "Artisan"],
        ["Which command creates a controller?", "Controller"],
        ["Which command starts the development server?", "Serve"],
        ["Which command runs migrations?", "Migrate"],
        ["Which command generates a model?", "Model"],
        ["Which command publishes vendor files?", "Publish"],
        ["Which command caches config files?", "Config"],
        ["Which command generates an application key?", "Keygen"],
        ["Which command creates middleware?", "Middleware"],
        ["Which command refreshes migrations?", "Refresh"],
    
        // Caching & Queues
        ["What is Laravel’s default cache driver?", "File"],
        ["What is the default queue driver?", "Redis"],
        ["Which command clears cache?", "Cacheclear"],
        ["Which command runs queue jobs?", "Queuework"],
        ["What unit is used for cache expiration?", "Minutes"],
        ["What is the default queue connection?", "Sync"],
        ["Which command restarts queues?", "Restart"],
        ["Which command flushes cache?", "Flush"],
        ["Which method dispatches a job?", "Dispatch"],
        ["Which method delays a job?", "Delay"],
    
        // Sessions & Cookies
        ["What is the default session driver?", "File"],
        ["Which method retrieves session data?", "Sessionget"],
        ["Which method stores session data?", "Sessionput"],
        ["Which key encrypts data?", "Key"],
        ["Which method checks session existence?", "Sessionhas"],
        ["Which method removes a session item?", "Forget"],
        ["Which method regenerates the session ID?", "Regenerate"],
        ["Which driver stores sessions in the database?", "Database"],
        ["Which command flushes all session data?", "Sessionflush"],
        ["Which helper function manages cookies?", "Cookie"],
    
        // Testing & Debugging
        ["Which tool is used for testing in Laravel?", "Pest"],
        ["Which command runs unit tests?", "Phpunit"],
        ["Which function dumps and dies?", "Ddd"],
        ["Which method writes logs?", "Log"],
        ["Which command clears logs?", "Logclear"],
        ["Which factory method generates fake data?", "Factory"],
        ["Which method mocks HTTP requests?", "Http"],
        ["Which method stores log messages?", "Loginfo"],
        ["Which command resets migrations?", "Reset"],
        ["Which helper function runs a test?", "Test"],
    
        // Security & Deployment
        ["Which file stores environment variables?", "Env"],
        ["Which method generates API tokens?", "Token"],
        ["Which directive prevents CSRF attacks?", "Csrf"],
        ["Which command clears compiled views?", "Viewclear"],
        ["Which command caches configuration files?", "Config"],
        ["Which tool helps deploy Laravel apps?", "Forge"],
        ["Which Laravel service runs in the cloud?", "Vapor"],
        ["Which method encrypts data?", "Encrypt"],
        ["Which method generates signed URLs?", "Signedurl"],
        ["Which command optimizes the app?", "Optimize"],
    
        // Miscellaneous
        ["What is Laravel's task scheduler called?", "Scheduler"],
        ["Which tool manages Laravel packages?", "Composer"],
        ["Which lightweight Laravel UI package exists?", "Breeze"],
        ["Which class handles HTTP requests?", "Request"],
        ["Which feature enables real-time broadcasting?", "Broadcast"],
        ["Which command checks the Laravel version?", "Version"],
        ["Which function retrieves config values?", "Config"],
        ["Which command handles queue jobs?", "Queue"],
        ["Which command creates a seeder?", "Makeseeder"],
        ["Which table stores authentication users?", "Users"],

        ["Which method binds a model to a route?", "Binding"],
        ["What is the default namespace for routes?", "AppHttp"],
        ["Which method adds conditions to routes?", "Where"],
        ["Which middleware prevents back history?", "Prevent"],
        ["Which class resolves route dependencies?", "Controller"],
        ["What is the fallback method for 404 routes?", "Fallback"],
        
        // Advanced Eloquent
        ["Which method defines an inverse relation?", "BelongsTo"],
        ["Which method returns deleted models?", "WithTrashed"],
        ["Which method creates related models?", "SaveMany"],
        ["Which method adds global scope?", "AddingScope"],
        ["Which query method executes raw SQL?", "SelectRaw"],
        ["Which method prevents attribute modification?", "Guarded"],
        ["Which trait enables UUIDs in models?", "HasUuid"],
        ["Which Eloquent event runs before saving?", "Saving"],
        
        // Blade & Views
        ["Which Blade directive checks user roles?", "Can"],
        ["Which directive continues a loop?", "Continue"],
        ["Which function retrieves a language line?", "Trans"],
        ["Which directive checks user permissions?", "Gate"],
        ["Which directive is used for JSON encoding?", "Json"],
        
        // Caching & Performance
        ["Which cache driver is fastest?", "Redis"],
        ["Which method checks if cache exists?", "Has"],
        ["Which method retrieves expired cache?", "Remember"],
        ["Which cache method stores values forever?", "Forever"],
        ["Which method retrieves the cache TTL?", "GetTtl"],
        
        // Security & Authentication
        ["Which middleware forces HTTPS?", "Secure"],
        ["Which method generates hashed tokens?", "HashMake"],
        ["Which Laravel feature prevents XSS?", "Escape"],
        ["Which method regenerates auth tokens?", "Refresh"],
        ["Which command generates passport keys?", "Passport"],
        
        // Testing & Debugging
        ["Which method simulates HTTP requests?", "Fake"],
        ["Which method checks logs in tests?", "AssertLog"],
        ["Which function dumps variables in Laravel?", "Ddd"],
        ["Which testing method mocks requests?", "Mock"],
        ["Which method checks if a file exists?", "Exists"],
        
        // Queues & Jobs
        ["Which queue driver handles instant jobs?", "Sync"],
        ["Which method releases a job back to queue?", "Release"],
        ["Which command retries failed jobs?", "Retry"],
        ["Which method delays job execution?", "Later"],
        ["Which command starts a queue worker?", "Queuework"],

        // Deployment & Optimization
        ["Which method reduces queries in loops?", "EagerLoad"],
        ["Which command compiles Blade templates?", "ViewCache"],
        ["Which command clears route cache?", "Routeclear"],
        ["Which command removes compiled files?", "ClearCache"],
        ["Which tool deploys Laravel to AWS?", "Vapor"]
    ];
    

    public function run(): void
    {
        try {
            DB::beginTransaction();

            for ($i = 0; $i < 50; $i++) {
                // Create a 10x10 puzzle
                $puzzle = Puzzle::create([
                    'title' => 'Laravel Mastery',
                    'description' => 'Test your Laravel knowledge with this crossword puzzle featuring core concepts, functions, and terminology.',
                    'grid_size' => 10,
                    'time_limit' => 10,
                    'is_active' => true,
                ]);

                // Initialize crossword generator
                $generator = new CrosswordGenerator(10);

                // Shuffle the Q&A pool to get random questions each time
                shuffle($this->qaPool);

                // Add words to the generator
                foreach ($this->qaPool as $qa) {
                    if (strlen($qa[1]) <= 5) {  // Ensure word fits in grid
                        $generator->addWord($qa[0], $qa[1]);
                    }
                }

                // Generate the crossword
                $result = $generator->generate();

                // Store grid data
                $puzzle->grid_data = $result['grid'];
                $puzzle->save();

                // Create clues
                foreach ($result['clues'] as $clue) {
                    Clue::create([
                        'puzzle_id' => $puzzle->id,
                        'question' => $clue['question'],
                        'answer' => $clue['answer'],
                        'direction' => $clue['direction'],
                        'start_position_x' => $clue['start_x'],
                        'start_position_y' => $clue['start_y'],
                        'number' => $clue['number'],
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to seed puzzle: ' . $e->getMessage());
            throw $e;
        }
    }
}