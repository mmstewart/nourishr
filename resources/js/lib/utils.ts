import type { ClassValue } from "clsx"
import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    const cleanPath = (url: string) => url.split('?')[0];
    
    const normalizedCheck = cleanPath(toUrl(urlToCheck));
    const normalizedCurrent = cleanPath(currentUrl);

    if (normalizedCheck === normalizedCurrent) return true;
    if (normalizedCheck !== '/' && normalizedCurrent.startsWith(normalizedCheck + '/')) return true;

    return false;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
  return typeof href === 'string' ? href : href?.url;
}