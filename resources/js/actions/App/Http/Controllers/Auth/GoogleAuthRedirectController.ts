import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\GoogleAuthRedirectController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthRedirectController.php:10
* @route '/auth/google/redirect'
*/
const GoogleAuthRedirectController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GoogleAuthRedirectController.url(options),
    method: 'get',
})

GoogleAuthRedirectController.definition = {
    methods: ["get","head"],
    url: '/auth/google/redirect',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\GoogleAuthRedirectController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthRedirectController.php:10
* @route '/auth/google/redirect'
*/
GoogleAuthRedirectController.url = (options?: RouteQueryOptions) => {
    return GoogleAuthRedirectController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\GoogleAuthRedirectController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthRedirectController.php:10
* @route '/auth/google/redirect'
*/
GoogleAuthRedirectController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GoogleAuthRedirectController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthRedirectController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthRedirectController.php:10
* @route '/auth/google/redirect'
*/
GoogleAuthRedirectController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: GoogleAuthRedirectController.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthRedirectController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthRedirectController.php:10
* @route '/auth/google/redirect'
*/
const GoogleAuthRedirectControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: GoogleAuthRedirectController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthRedirectController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthRedirectController.php:10
* @route '/auth/google/redirect'
*/
GoogleAuthRedirectControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: GoogleAuthRedirectController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GoogleAuthRedirectController::__invoke
* @see app/Http/Controllers/Auth/GoogleAuthRedirectController.php:10
* @route '/auth/google/redirect'
*/
GoogleAuthRedirectControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: GoogleAuthRedirectController.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

GoogleAuthRedirectController.form = GoogleAuthRedirectControllerForm

export default GoogleAuthRedirectController