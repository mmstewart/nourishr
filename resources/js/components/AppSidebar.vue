<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Apple, House, Mail, Scale, Send, Sparkles } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

const allMainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: House,
    },
    {
        title: 'Weight Entries',
        href: '/weight-entries',
        icon: Scale,
    },
    {
        title: 'Nutrition Entries',
        href: '/nutrition-entries',
        icon: Apple,
    },
    {
        title: 'Coach',
        href: '/coach',
        icon: Sparkles,
    },
];

// Hide the Coach link when no AI provider key is configured.
const mainNavItems = computed(() =>
    allMainNavItems.filter((item) => item.href !== '/coach' || page.props.coachEnabled),
);

const footerNavItems: NavItem[] = [
    {
        title: 'Feedback',
        href: '/feedback',
        icon: Send,
    },
    {
        title: 'Contact',
        href: '/contact',
        icon: Mail,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        class="flex items-center group-data-[collapsible=icon]:justify-center"
                        as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
