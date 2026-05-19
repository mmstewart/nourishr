import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
const SetupAccountController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: SetupAccountController.url(options),
    method: 'post',
})

SetupAccountController.definition = {
    methods: ["post"],
    url: '/setup-account',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
SetupAccountController.url = (options?: RouteQueryOptions) => {
    return SetupAccountController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
SetupAccountController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: SetupAccountController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
const SetupAccountControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: SetupAccountController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
SetupAccountControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: SetupAccountController.url(options),
    method: 'post',
})

SetupAccountController.form = SetupAccountControllerForm

export default SetupAccountController