<?php

namespace Tests\Feature;

use App\Mail\ContactReceived;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_screen_can_be_rendered()
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
    }

    public function test_contact_page_knows_if_user_is_logged_in()
    {
        $this->get(route('contact'))
            ->assertInertia(
                fn($page) => $page
                    ->where('isLoggedIn', false)
            );

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('contact'))
            ->assertInertia(
                fn($page) => $page
                    ->where('isLoggedIn', true)
            );
    }

    public function test_guest_user_can_submit_contact_form()
    {
        Mail::fake();

        $formData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Hello, I need some help with my macros.',
        ];

        $response = $this->post(route('contact.store'), $formData);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');

        Mail::assertSent(ContactReceived::class);
    }

    public function test_authenticated_user_can_submit_contact_form()
    {
        Mail::fake();

        $this->seed();
        $user = User::factory()->completed()->create();

        $response = $this->actingAs($user)->post(route('contact.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'message' => 'I am a logged in user asking for help.',
        ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        Mail::assertSent(ContactReceived::class);
    }

    public function test_contact_form_rejects_spam_bots_as_guest()
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Botty',
            'email' => 'bot@spam.com',
            'message' => 'Authenticated Spam!',
            'company' => 'Bot Corporation Company!',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
    }

    public function test_contact_form_rejects_spam_bots_as_user()
    {
        $user = User::factory()->completed()->create();

        $response = $this->actingAs($user)->post(route('contact.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'message' => 'Authenticated Spam!',
            'company' => 'Bot Corporation Company!',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
    }

    public function test_contact_form_throttles_guests()
    {
        $formData = [
            'name' => 'User',
            'email' => 'u@e.com',
            'message' => 'Hi, this is a message!'
        ];

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('contact.store'), $formData);
        }

        $response = $this->post(route('contact.store'), $formData);

        $response->assertStatus(429);
    }

    public function test_contact_form_throttles_users()
    {
        $user = User::factory()->completed()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)->post(route('contact.store'), [
                'name' => $user->name,
                'email' => $user->email,
                'message' => 'Spamming...'
            ]);
        }

        $this->actingAs($user)->post(route('contact.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'message' => 'One too many'
        ])->assertStatus(429);
    }
}
