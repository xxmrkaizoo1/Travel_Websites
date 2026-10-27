<?php

namespace Tests\Feature;

use App\Models\ChinaMuslimPackage;
use App\Models\PackageLike;
use App\Models\User;
use App\Support\PackageLikes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_asked_to_log_in_and_come_back_to_the_package_afterwards(): void
    {
        $key = PackageLikes::key(ChinaMuslimPackage::factory()->create());

        $this->get(route('china-muslim'))->assertOk();

        $this->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertUnauthorized()
            ->assertJson([
                'login_url' => route('client.login'),
                'register_url' => route('client.register'),
            ])
            ->assertSessionHas('url.intended', route('china-muslim'));

        $this->assertDatabaseCount('package_likes', 0);
    }

    public function test_admins_cannot_like_packages(): void
    {
        $key = PackageLikes::key(ChinaMuslimPackage::factory()->create());

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertForbidden();

        $this->assertDatabaseCount('package_likes', 0);
    }

    public function test_client_likes_and_unlikes_a_package(): void
    {
        $key = PackageLikes::key(ChinaMuslimPackage::factory()->create());
        $client = User::factory()->create();

        $this->actingAs($client)
            ->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertExactJson(['count' => 1, 'liked' => true]);

        $this->actingAs($client)
            ->getJson(route('package-likes.index', ['keys' => [$key]]))
            ->assertExactJson([$key => ['count' => 1, 'liked' => true]]);

        $this->actingAs($client)
            ->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertExactJson(['count' => 0, 'liked' => false]);

        $this->assertDatabaseCount('package_likes', 0);
    }

    public function test_each_client_counts_once_and_sees_only_their_own_like(): void
    {
        $key = PackageLikes::key(ChinaMuslimPackage::factory()->create());
        PackageLike::query()->create(['package_key' => $key, 'visitor_key' => 'user:'.User::factory()->create()->id]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('package-likes.toggle'), ['key' => $key])
            ->assertExactJson(['count' => 2, 'liked' => true]);

        $this->assertDatabaseHas('package_likes', ['package_key' => $key, 'visitor_key' => 'user:'.$user->id]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('package-likes.index', ['keys' => [$key]]))
            ->assertExactJson([$key => ['count' => 2, 'liked' => false]]);
    }

    public function test_likes_are_only_accepted_for_real_packages_and_destinations(): void
    {
        $chinaPackage = ChinaMuslimPackage::factory()->create();
        $this->actingAs(User::factory()->create());

        foreach ([
            'china-muslim-package/'.($chinaPackage->id + 100),
            'china-muslim-package/'.$chinaPackage->id.'#extra',
            'tour-package/does-not-exist',
            'tour-package/series-departures-2026#atlantis',
            'user/1',
        ] as $key) {
            $this->postJson(route('package-likes.toggle'), ['key' => $key])->assertJsonValidationErrors('key');
        }

        $this->assertDatabaseCount('package_likes', 0);
    }

    public function test_like_buttons_appear_on_smart_muslim_packages(): void
    {
        $chinaPackage = ChinaMuslimPackage::factory()->create();

        $this->get(route('china-muslim'))
            ->assertSee('data-like-key="'.PackageLikes::key($chinaPackage).'"', false);

        $this->get(route('home'))
            ->assertSee('data-like-key="tour-package/series-departures-2026#', false);

        $this->get(route('tour-package.show', 'series-departures-2026'))
            ->assertSee('data-like-key="tour-package/series-departures-2026#', false);

        $this->actingAs(User::factory()->create())
            ->postJson(route('package-likes.toggle'), ['key' => 'tour-package/series-departures-2026#da-nang'])
            ->assertOk()
            ->assertJson(['liked' => true]);
    }
}
