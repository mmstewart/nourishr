import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\SetupController::__invoke
* @see app/Http/Controllers/Auth/SetupController.php:13
* @route '/setup'
*/
const SetupController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SetupController.url(options),
    method: 'get',
})

SetupController.definition = {
    methods: ["get","head"],
    url: '/setup',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\SetupController::__invoke
* @see app/Http/Controllers/Auth/SetupController.php:13
* @route '/setup'
*/
SetupController.url = (options?: RouteQueryOptions) => {
    return SetupController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SetupController::__invoke
* @see app/Http/Controllers/Auth/SetupController.php:13
* @route '/setup'
*/
SetupController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SetupController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\SetupController::__invoke
* @see app/Http/Controllers/Auth/SetupController.php:13
* @route '/setup'
*/
SetupController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: SetupController.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\SetupController::__invoke
* @see app/Http/Controllers/Auth/SetupController.php:13
* @route '/setup'
*/
const SetupControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: SetupController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\SetupController::__invoke
* @see app/Http/Controllers/Auth/SetupController.php:13
* @route '/setup'
*/
SetupControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: SetupController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\SetupController::__invoke
* @see app/Http/Controllers/Auth/SetupController.php:13
* @route '/setup'
*/
SetupControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: SetupController.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

SetupController.form = SetupControllerForm

export default SetupController