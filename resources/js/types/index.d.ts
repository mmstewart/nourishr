import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    flash: { success: string, error: string };
};

export interface User {
    id: number;
    name: string;
    dob:string;
    height_inches: number;
    gender: string;
    country_code: string;
    timezone: string;
    email: string;
    email_verified_at: string | null;
    has_password: boolean;
    weight_goal: WeightGoal;
    nutrition_entries: NutritionEntry[];
    weight_entries: WeightEntry[];
    created_at: string;
    updated_at: string;
}

export interface WeightGoal {
    id: number;
    user_id: number;
    start_date: string;
    start_weight: number;
    current_weight: number;
    goal_weight: number;
    weekly_goal_id: number;
    activity_level_id: number;
    bmr: number;
    maintenance_calories: number;
    target_calories: number;
    protein_grams: number;
    fat_grams: number;
    carb_grams: number;
    created_at: string;
    updated_at: string;
}

export interface NutritionEntry {
    id: number;
    date: string;
    date_raw: string;
    calories: number;
    food_item: string;
    protein: number;
    carbohydrates: number;
    fats: number;
    created_at: string;
    updated_at: string;
}

export interface WeightEntry {
    id: number;
    user_id: number;
    date: string;
    formatted_date: string;
    chart_date: string;
    weight: number;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
