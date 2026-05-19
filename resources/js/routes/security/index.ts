import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\SecurityController::show
* @see app/Http/Controllers/Settings/SecurityController.php:23
* @route '/settings/security'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/settings/security',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\SecurityController::show
* @see app/Http/Controllers/Settings/SecurityController.php:23
* @route '/settings/security'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\SecurityController::show
* @see app/Http/Controllers/Settings/SecurityController.php:23
* @route '/settings/security'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SecurityController::show
* @see app/Http/Controllers/Settings/SecurityController.php:23
* @route '/settings/security'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\SecurityController::show
* @see app/Http/Controllers/Settings/SecurityController.php:23
* @route '/settings/security'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SecurityController::show
* @see app/Http/Controllers/Settings/SecurityController.php:23
* @route '/settings/security'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SecurityController::show
* @see app/Http/Controllers/Settings/SecurityController.php:23
* @route '/settings/security'
*/
showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Settings\SecurityController::destroy
* @see app/Http/Controllers/Settings/SecurityController.php:33
* @route '/settings/security'
*/
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/settings/security',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Settings\SecurityController::destroy
* @see app/Http/Controllers/Settings/SecurityController.php:33
* @route '/settings/security'
*/
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\SecurityController::destroy
* @see app/Http/Controllers/Settings/SecurityController.php:33
* @route '/settings/security'
*/
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Settings\SecurityController::destroy
* @see app/Http/Controllers/Settings/SecurityController.php:33
* @route '/settings/security'
*/
const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\SecurityController::destroy
* @see app/Http/Controllers/Settings/SecurityController.php:33
* @route '/settings/security'
*/
destroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const security = {
    show: Object.assign(show, show),
    destroy: Object.assign(destroy, destroy),
}

export default security