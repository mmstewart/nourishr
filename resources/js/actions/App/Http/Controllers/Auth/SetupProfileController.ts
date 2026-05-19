import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
const SetupProfileController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: SetupProfileController.url(options),
    method: 'post',
})

SetupProfileController.definition = {
    methods: ["post"],
    url: '/setup-profile',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
SetupProfileController.url = (options?: RouteQueryOptions) => {
    return SetupProfileController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
SetupProfileController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: SetupProfileController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
const SetupProfileControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: SetupProfileController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
SetupProfileControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: SetupProfileController.url(options),
    method: 'post',
})

SetupProfileController.form = SetupProfileControllerForm

export default SetupProfileController