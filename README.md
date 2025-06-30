# Laravel Action Pattern Example

This project demonstrates the implementation of the **action pattern** in a Laravel application, inspired by the command pattern to encapsulate business logic into reusable, maintainable classes. It showcases how actions can ensure consistency, manage database transactions, and prepare for future scalability (e.g., API integration). The primary use case is creating a user and their associated profile in a single, atomic operation.

## Purpose
The action pattern is used to:
- **Encapsulate Business Logic**: Separate complex logic from controllers into dedicated action classes for better maintainability.
- **Ensure Consistency**: Provide a standardized approach for development teams to handle features uniformly.
- **Manage Database Transactions**: Use Laravel's `DB::transaction` to ensure data integrity during multi-step operations.
- **Support Scalability**: Enable shared logic between web and API layers, future-proofing the application.
- **Facilitate Chaining**: Allow actions to call other actions for modular, complex workflows.

## Project Structure
The project follows a clean directory structure to organize models, actions, form requests, and controllers:

```
app/
├── Actions/
│   ├── CreateUserAction.php     # Handles user creation and chains profile creation
│   └── CreateProfileAction.php  # Handles profile creation for a user
├── Http/
│   ├── Requests/
│   │   └── CreateUserRequest.php  # Validates user and profile data
│   └── Controllers/
│       └── UserController.php    # Orchestrates the creation process
├── Models/
│   ├── User.php                  # User model with profile relationship
│   └── Profile.php               # Profile model linked to a user
routes/
└── api.php                       # API route for user creation
```

## Key Components
1. **Models**:
   - `User`: Represents a user with fields `name`, `email`, and `password`. Has a one-to-one relationship with `Profile`.
   - `Profile`: Stores additional user information (`bio`, `location`) linked to a `User`.

2. **Actions**:
   - `CreateUserAction`: Creates a user and calls `CreateProfileAction` within a database transaction to ensure atomicity.
   - `CreateProfileAction`: Creates a profile for a given user, encapsulating profile-specific logic.

3. **Form Request**:
   - `CreateUserRequest`: Validates input data for user and profile creation, ensuring data integrity before processing.

4. **Controller**:
   - `UserController`: Handles HTTP requests, delegates logic to `CreateUserAction`, and returns JSON responses.

5. **Routes**:
   - An API route (`POST /users`) is defined to trigger the user creation process.

## Workflow
1. A client sends a `POST` request to `/users` with user and profile data.
2. The `CreateUserRequest` validates the input (e.g., `name`, `email`, `password`, `profile_data.bio`, `profile_data.location`).
3. The `UserController` receives the validated data and invokes `CreateUserAction`.
4. `CreateUserAction`:
   - Creates a `User` record.
   - Calls `CreateProfileAction` to create the associated `Profile`.
   - Wraps both operations in a `DB::transaction` to ensure atomicity (if any step fails, all changes are rolled back).
5. The controller returns a JSON response with the created user, profile, and a success message.

## Setup Instructions
### Prerequisites
- PHP >= 8.1
- Laravel >= 10
- Composer
- A database (e.g., MySQL, SQLite)

### Installation
1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd <repository-directory>
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment**:
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update `.env` with your database credentials:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=your_database
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**:
   - Ensure you have migrations for the `users` and `profiles` tables. Example migrations:
     ```php
     // database/migrations/xxxx_create_users_table.php
     Schema::create('users', function (Blueprint $table) {
         $table->id();
         $table->string('name');
         $table->string('email')->unique();
         $table->string('password');
         $table->timestamps();
     });

     // database/migrations/xxxx_create_profiles_table.php
     Schema::create('profiles', function (Blueprint $table) {
         $table->id();
         $table->foreignId('user_id')->constrained()->onDelete('cascade');
         $table->text('bio');
         $table->string('location');
         $table->timestamps();
     });
     ```
   - Run migrations:
     ```bash
     php artisan migrate
     ```

6. **Start the Development Server**:
   ```bash
   php artisan serve
   ```

7. **Test the API**:
   - Use a tool like Postman or cURL to send a `POST` request to `http://localhost:8000/api/users`:
     ```json
     {
         "name": "John Doe",
         "email": "john@example.com",
         "password": "password123",
         "profile_data": {
             "bio": "Software developer",
             "location": "New York"
         }
     }
     ```
   - Expected response (HTTP 201):
     ```json
     {
         "user": {
             "id": 1,
             "name": "John Doe",
             "email": "john@example.com",
             "created_at": "...",
             "updated_at": "..."
         },
         "profile": {
             "id": 1,
             "user_id": 1,
             "bio": "Software developer",
             "location": "New York",
             "created_at": "...",
             "updated_at": "..."
         },
         "message": "User and profile created successfully"
     }
     ```

## Testing
To ensure the action pattern and transactional behavior work as expected:
1. Install PHPUnit and Laravel's testing dependencies:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. Write tests for `CreateUserAction` and `UserController`. Example:
   ```php
   // tests/Feature/CreateUserTest.php
   use App\Actions\CreateUserAction;
   use App\Models\User;
   use Tests\TestCase;

   class CreateUserTest extends TestCase
   {
       public function test_user_and_profile_creation()
       {
           $response = $this->postJson('/api/users', [
               'name' => 'John Doe',
               'email' => 'john@example.com',
               'password' => 'password123',
               'profile_data' => [
                   'bio' => 'Test bio',
                   'location' => 'Test location',
               ],
           ]);

           $response->assertStatus(201)
                    ->assertJson(['message' => 'User and profile created successfully']);
           $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
           $this->assertDatabaseHas('profiles', ['bio' => 'Test bio']);
       }
   }
   ```
3. Run tests:
   ```bash
   php artisan test
   ```

## Additional Notes
- **Error Handling**: The actions and controller could be enhanced with try-catch blocks to handle exceptions gracefully and return user-friendly error messages.
- **Scalability**: The action pattern supports adding more actions for complex workflows (e.g., creating related resources like roles or permissions).
- **API Readiness**: The logic in actions can be reused for API endpoints, ensuring consistency if the application expands to include a public API.
- **Security**: Consider adding authentication middleware (`auth:api`) to the route if user creation is restricted.

## Contributing
Contributions are welcome! Please:
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -m 'Add your feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

## License
This project is licensed under the MIT License.
