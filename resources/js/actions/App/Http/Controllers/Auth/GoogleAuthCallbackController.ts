import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\GoogleAuthCallbackController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthCallbackController.php:12
* @route '/auth/google/callback'
*/
const GoogleAuthCallbackController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GoogleAuthCallbackController.url(options),
    method: 'get',
})

GoogleAuthCallbackController.definition = {
    methods: ["get","head"],
    url: '/auth/google/callback',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\GoogleAuthCallbackController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthCallbackController.php:12
* @route '/auth/google/callback'
*/
GoogleAuthCallbackController.url = (options?: RouteQueryOptions) => {
    return GoogleAuthCallbackController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\GoogleAuthCallbackController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthCallbackController.php:12
* @route '/auth/google/callback'
*/
GoogleAuthCallbackController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GoogleAuthCallbackController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthCallbackController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthCallbackController.php:12
* @route '/auth/google/callback'
*/
GoogleAuthCallbackController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: GoogleAuthCallbackController.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthCallbackController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthCallbackController.php:12
* @route '/auth/google/callback'
*/
const GoogleAuthCallbackControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: GoogleAuthCallbackController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthCallbackController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthCallbackController.php:12
* @route '/auth/google/callback'
*/
GoogleAuthCallbackControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: GoogleAuthCallbackController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthCallbackController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthCallbackController.php:12
* @route '/auth/google/callback'
*/
GoogleAuthCallbackControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: GoogleAuthCallbackController.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

GoogleAuthCallbackController.form = GoogleAuthCallbackControllerForm

export default GoogleAuthCallbackController