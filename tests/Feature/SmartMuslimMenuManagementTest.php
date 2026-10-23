<?php

namespace Tests\Feature;

use App\Models\SmartMuslimMenu;
use App\Models\TourPackagePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartMuslimMenuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_dropdown_and_text_only_submenu_labels_for_both_navbars(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.smart-muslim.menus.index'))
            ->assertOk()
            ->assertSee('Content management')
            ->assertSee('Manage existing content')
            ->assertSee('Smart Muslim homepage')
            ->assertSee('Tour Package (Private)')
            ->assertSee('Tour Package (Series)')
            ->assertSee('China Muslim')
            ->assertSee('Company Trip (MICE)')
            ->assertSee('Smart Muslim navigation')
            ->assertSee('href="'.route('admin.homepage').'"', false)
            ->assertSee('href="'.route('admin.homepage.services').'"', false)
            ->assertSee('href="'.route('admin.china-muslim').'"', false)
            ->assertSee('href="'.route('admin.mice').'"', false)
            ->assertSee('Create a dropdown')
            ->assertSee('data-menu-icon="compass"', false)
            ->assertDontSee('🕌')
            ->assertDontSee('🕋');

        $this->post(route('admin.smart-muslim.menus.store'), [
            'label' => 'Special Packages',
            'icon' => 'mosque',
        ])
            ->assertRedirect(route('admin.smart-muslim.menus.index'))
            ->assertSessionHas('status', 'Smart Muslim menu created.');

        $menu = SmartMuslimMenu::query()->whereNull('parent_id')->where('label', 'Special Packages')->firstOrFail();
        $this->assertSame('mosque', $menu->icon);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-menu-icon="mosque"', false)
            ->assertSee('<svg', false)
            ->assertDontSee('🕌')
            ->assertSee('data-context-menu="tour-package-menu" data-page-name="Smart Muslim Homepage content"', false)
            ->assertSee('data-context-menu="tour-package-menu" data-page-name="China Muslim"', false)
            ->assertSee('data-context-menu="tour-package-menu" data-page-name="Company Trip (MICE)"', false)
            ->assertSee('data-navigation-parent-name="Tour Package (Private)"', false)
            ->assertSee('data-navigation-parent-name="Tour Package (Series)"', false)
            ->assertSee('data-context-create-url="'.route('admin.smart-muslim.menus.pages.create', $menu).'"', false)
            ->assertSee('href="'.route('admin.smart-muslim.menus.pages.create', $menu).'"', false)
            ->assertSee('＋ New page');

        $this->get(route('admin.smart-muslim.menus.pages.create', $menu))
            ->assertOk()
            ->assertSee('SMART MUSLIM TOUR · Special Packages')
            ->assertSee('action="'.route('admin.smart-muslim.menus.pages.store', $menu).'"', false);

        $this->post(route('admin.smart-muslim.menus.pages.store', $menu), [
            'name' => 'Umrah Packages',
            'kind' => 'introduction',
        ])->assertRedirect(route('admin.tour-packages.edit', 'umrah-packages'));

        $page = TourPackagePage::query()->where('slug', 'umrah-packages')->firstOrFail();
        $this->assertTrue($page->is_custom);
        $this->assertSame($menu->id, $page->smart_muslim_menu_id);
        $this->assertNull($page->parent_slug);

        $this->post(route('admin.smart-muslim.menus.items.store', $menu), ['label' => 'Family Tours'])
            ->assertRedirect(route('admin.smart-muslim.menus.index'))
            ->assertSessionHas('status', 'Submenu label added.');

        $child = $menu->children()->where('label', 'Family Tours')->firstOrFail();
        $this->assertSame($menu->id, $child->parent_id);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('aria-controls="smart-muslim-menu-'.$menu->id.'"', false)
            ->assertSee('class="submenu-trigger" type="button" aria-expanded="false" aria-controls="smart-muslim-menu-'.$menu->id.'"', false)
            ->assertSee('<span>'.$menu->label.'</span>', false)
            ->assertSee('<span class="submenu-arrow" aria-hidden="true">›</span>', false)
            ->assertDontSee('data-menu-icon="mosque"', false)
            ->assertDontSee('🕌')
            ->assertSee('Special Packages')
            ->assertSee('Family Tours')
            ->assertSee('href="'.route('tour-package.show', $page->slug).'"', false)
            ->assertSee('Umrah Packages')
            ->assertDontSee('<a class="smart-muslim-menu-label"', false);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Special Packages')
            ->assertSee('Family Tours')
            ->assertSee('Umrah Packages')
            ->assertSee('href="'.route('admin.tour-packages.edit', $page).'"', false)
            ->assertSee('data-delete-label="Remove submenu label"', false)
            ->assertSee('data-context-create-label="Create another page"', false)
            ->assertSee('data-delete-url="'.route('admin.tour-packages.destroy', $page).'"', false)
            ->assertSee('href="'.route('admin.smart-muslim.menus.index').'"', false);

        $this->get(route('admin.tour-packages.edit', $page))
            ->assertOk()
            ->assertSee('Shown inside Smart Muslim Tour → Special Packages')
            ->assertDontSee('name="parent_slug"', false);

        $this->putJson(route('admin.tour-packages.move', $page), ['parent_slug' => null])
            ->assertNotFound();
    }

    public function test_admin_can_change_a_dropdown_label_and_icon(): void
    {
        $this->actingAs($this->createAdmin());
        $menu = SmartMuslimMenu::query()->create([
            'label' => 'Old label',
            'icon' => 'compass',
        ]);

        $this->put(route('admin.smart-muslim.menus.update', $menu), [
            'label' => 'New label',
            'icon' => 'kaaba',
        ])->assertRedirect(route('admin.smart-muslim.menus.index'))
            ->assertSessionHas('status', 'Menu updated.');

        $this->assertDatabaseHas('smart_muslim_menus', [
            'id' => $menu->id,
            'label' => 'New label',
            'icon' => 'kaaba',
        ]);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('class="submenu-trigger" type="button" aria-expanded="false" aria-controls="smart-muslim-menu-'.$menu->id.'"', false)
            ->assertDontSee('data-menu-icon="kaaba"', false)
            ->assertSee('New label')
            ->assertDontSee('Old label');
    }

    public function test_menu_rejects_an_icon_outside_the_available_icon_choices(): void
    {
        $this->actingAs($this->createAdmin());
        $menu = SmartMuslimMenu::query()->create([
            'label' => 'Keep this',
            'icon' => 'compass',
        ]);

        $this->from(route('admin.smart-muslim.menus.index'))
            ->put(route('admin.smart-muslim.menus.update', $menu), [
                'label' => 'Changed',
                'icon' => '<script>',
            ])
            ->assertRedirect(route('admin.smart-muslim.menus.index'))
            ->assertSessionHasErrors('icon');

        $this->assertDatabaseHas('smart_muslim_menus', [
            'id' => $menu->id,
            'label' => 'Keep this',
            'icon' => 'compass',
        ]);
    }

    public function test_guests_and_non_admins_cannot_create_pages_inside_smart_muslim_menus(): void
    {
        $menu = SmartMuslimMenu::query()->create(['label' => 'Private']);

        $this->get(route('admin.smart-muslim.menus.pages.create', $menu))
            ->assertRedirect(route('login'));

        $this->post(route('admin.smart-muslim.menus.pages.store', $menu), [
            'name' => 'Private Page',
            'kind' => 'introduction',
        ])->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.smart-muslim.menus.pages.create', $menu))
            ->assertForbidden();

        $this->post(route('admin.smart-muslim.menus.pages.store', $menu), [
            'name' => 'Private Page',
            'kind' => 'introduction',
        ])->assertForbidden();

        $this->assertDatabaseMissing('tour_package_pages', ['slug' => 'private-page']);
    }

    public function test_nested_menu_label_cannot_be_used_as_a_page_parent(): void
    {
        $this->actingAs($this->createAdmin());
        $parent = SmartMuslimMenu::query()->create(['label' => 'Parent']);
        $child = $parent->children()->create(['label' => 'Child']);

        $this->get(route('admin.smart-muslim.menus.pages.create', $child))
            ->assertNotFound();

        $this->post(route('admin.smart-muslim.menus.pages.store', $child), [
            'name' => 'Nested Page',
            'kind' => 'introduction',
        ])->assertNotFound();

        $this->assertDatabaseMissing('tour_package_pages', ['slug' => 'nested-page']);
    }

    public function test_smart_muslim_page_cannot_be_assigned_a_tour_package_parent(): void
    {
        $this->actingAs($this->createAdmin());
        $menu = SmartMuslimMenu::query()->create(['label' => 'Special Packages']);

        $this->from(route('admin.smart-muslim.menus.pages.create', $menu))
            ->post(route('admin.smart-muslim.menus.pages.store', $menu), [
                'name' => 'Umrah Packages',
                'kind' => 'introduction',
                'parent_slug' => 'thailand',
            ])
            ->assertRedirect(route('admin.smart-muslim.menus.pages.create', $menu))
            ->assertSessionHasErrors('parent_slug');

        $this->assertDatabaseMissing('tour_package_pages', ['slug' => 'umrah-packages']);
    }

    public function test_menu_labels_are_escaped_in_the_public_navbar(): void
    {
        $menu = SmartMuslimMenu::query()->create(['label' => '<Special>']);
        $menu->children()->create(['label' => '<Family>']);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSee('&lt;Special&gt;', false)
            ->assertSee('&lt;Family&gt;', false)
            ->assertDontSee('<Special>', false)
            ->assertDontSee('<Family>', false);
    }

    public function test_guests_and_non_admins_cannot_manage_smart_muslim_menu_labels(): void
    {
        $this->get(route('admin.smart-muslim.menus.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.smart-muslim.menus.store'), ['label' => 'Private'])
            ->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.smart-muslim.menus.index'))
            ->assertForbidden();

        $this->post(route('admin.smart-muslim.menus.store'), ['label' => 'Private'])
            ->assertForbidden();

        $this->assertDatabaseCount('smart_muslim_menus', 0);
    }

    public function test_menu_labels_are_required_and_limited_to_80_characters(): void
    {
        $this->actingAs($this->createAdmin())
            ->from(route('admin.smart-muslim.menus.index'))
            ->post(route('admin.smart-muslim.menus.store'), ['label' => str_repeat('a', 81)])
            ->assertRedirect(route('admin.smart-muslim.menus.index'))
            ->assertSessionHasErrors('label');

        $this->assertDatabaseCount('smart_muslim_menus', 0);
    }

    public function test_only_top_level_menu_labels_can_have_submenu_labels(): void
    {
        $this->actingAs($this->createAdmin());
        $parent = SmartMuslimMenu::query()->create(['label' => 'Parent']);
        $child = $parent->children()->create(['label' => 'Child']);

        $this->post(route('admin.smart-muslim.menus.items.store', $child), ['label' => 'Nested'])
            ->assertNotFound();

        $this->assertDatabaseCount('smart_muslim_menus', 2);
    }

    public function test_deleting_a_dropdown_removes_its_submenu_labels(): void
    {
        $this->actingAs($this->createAdmin());
        $parent = SmartMuslimMenu::query()->create(['label' => 'Parent']);
        $child = $parent->children()->create(['label' => 'Child']);

        $this->delete(route('admin.smart-muslim.menus.destroy', $parent))
            ->assertRedirect(route('admin.smart-muslim.menus.index'));

        $this->assertDatabaseMissing('smart_muslim_menus', ['id' => $parent->id]);
        $this->assertDatabaseMissing('smart_muslim_menus', ['id' => $child->id]);
    }

    public function test_dropdown_with_pages_must_be_empty_before_deletion(): void
    {
        $this->actingAs($this->createAdmin());
        $menu = SmartMuslimMenu::query()->create(['label' => 'Packages']);
        $page = new TourPackagePage(TourPackagePage::starterContent(TourPackagePage::KIND_INTRODUCTION, 'Family Tour'));
        $page->slug = 'family-tour';
        $page->is_custom = true;
        $page->smart_muslim_menu_id = $menu->id;
        $page->save();

        $this->delete(route('admin.smart-muslim.menus.destroy', $menu))
            ->assertRedirect(route('admin.smart-muslim.menus.index'))
            ->assertSessionHasErrors('menu');

        $this->assertModelExists($menu);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
