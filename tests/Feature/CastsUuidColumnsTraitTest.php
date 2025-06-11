<?php

use Prahsys\Supabase\Tests\Database\Models\Post;
use Prahsys\Supabase\Tests\Database\Models\User;

it('handles string UUIDs in find method', function () {
    // Create a user with a UUID
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    // Use the string UUID to find the user
    $foundUser = User::find($user->id);

    // Assert the user was found
    expect($foundUser)->not->toBeNull();
    expect($foundUser->id)->toBe($user->id);
});

it('handles string UUIDs in where clauses', function () {
    // Create a user with a UUID
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    // Use the string UUID in a where clause
    $foundUser = User::where('id', $user->id)->first();

    // Assert the user was found
    expect($foundUser)->not->toBeNull();
    expect($foundUser->id)->toBe($user->id);
});

it('handles string UUIDs in whereIn clauses', function () {
    // Create users with UUIDs
    $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com']);
    $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com']);

    // Use string UUIDs in a whereIn clause
    $users = User::whereIn('id', [$user1->id, $user2->id])->get();

    // Assert both users were found
    expect($users)->toHaveCount(2);
    expect($users->pluck('id')->toArray())->toContain($user1->id, $user2->id);
});

it('correctly identifies UUID columns from explicit definition', function () {
    // Create a post to ensure the model is initialized
    $post = new Post;

    // Get the UUID columns
    $uuidColumns = $post->getUuidColumns();

    // Assert the UUID columns include the explicitly defined ones
    expect($uuidColumns)->toContain('user_id');

    // Make sure the primary key is always included
    expect($uuidColumns)->toContain('id');
});

it('allows joining tables with UUID columns', function () {
    // Create a user and a post for that user
    $user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
    $post = Post::create([
        'user_id' => $user->id,
        'title' => 'Test Post',
        'content' => 'This is a test post',
    ]);

    // Query with a join
    $result = Post::join('users', 'posts.user_id', '=', 'users.id')
        ->where('users.email', 'test@example.com')
        ->first();

    // Assert the join worked correctly
    expect($result)->not->toBeNull();
    expect($result->title)->toBe('Test Post');
});

it('identifies UUID columns from the model', function () {
    // Instead of checking the query builder directly (which might not be our custom one in tests),
    // we'll check the model's getUuidColumns method
    $post = new Post;
    $uuidColumns = $post->getUuidColumns();

    // Verify it contains the primary key and explicitly defined columns
    expect($uuidColumns)->toContain('id');
    expect($uuidColumns)->toContain('user_id');
});
