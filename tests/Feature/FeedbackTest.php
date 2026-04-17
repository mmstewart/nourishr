<?php

namespace Tests\Feature;

use App\Mail\FeedbackReceived;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_screen_can_be_rendered()
    {
        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('feedback'));

        $response->assertStatus(200);
    }

    public function test_feedback_page_has_authenticated_user()
    {
        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('feedback'))
            ->assertInertia(
                fn($page) => $page
                    ->has('auth.user')
                    ->where('auth.user.email', $user->email)
            );
    }

    public function test_authenticated_users_can_submit_feedback()
    {
        Mail::fake();

        $this->seed();

        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('feedback.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'message' => 'Your app is helping me hit my goals!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        Mail::assertSent(FeedbackReceived::class);
    }

    public function test_guests_cannot_submit_feedback()
    {
        $response = $this->post(route('feedback.store'), [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'message' => 'This should be blocked by auth middleware.',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_feedback_form_rejects_spam_bots()
    {
        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $spamData = [
            'name' => 'Botty McBot',
            'email' => 'bot@spam.com',
            'message' => 'Buy these things!',
            'phone_number' => '111-111-1111',
        ];

        $response = $this->actingAs($user)->post(route('feedback.store'), $spamData);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }

    public function test_feedback_form_throttles_excessive_requests()
    {
        $user = User::factory()->completed()->create();

        $user->weightGoal()->create([
            'weekly_goal_id' => 1,
            'activity_level_id' => 3,
            'start_weight' => 180,
            'current_weight' => 180,
            'goal_weight' => 160,
            'start_date' => now(),
        ]);

        $formData = [
            'name' => 'User',
            'email' => 'u@e.com',
            'message' => 'Hi, this is a message!',
        ];

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)->post(route('feedback.store'), $formData);
        }

        $response = $this->actingAs($user)->post(route('feedback.store'), $formData);

        $response->assertStatus(429);
    }
}
