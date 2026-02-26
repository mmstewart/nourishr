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
  if (toUrl(urlToCheck) === currentUrl) return true;

  // Remove query strings from both paths for comparison
  const cleanPath = (url: string) => url.split('?')[0];

  return cleanPath(toUrl(urlToCheck)) === cleanPath(currentUrl);
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
  return typeof href === 'string' ? href : href?.url;
}