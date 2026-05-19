import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
const SetupWeightGoalController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: SetupWeightGoalController.url(options),
    method: 'post',
})

SetupWeightGoalController.definition = {
    methods: ["post"],
    url: '/setup-weight-goal',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
SetupWeightGoalController.url = (options?: RouteQueryOptions) => {
    return SetupWeightGoalController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
SetupWeightGoalController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: SetupWeightGoalController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
const SetupWeightGoalControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: SetupWeightGoalController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
SetupWeightGoalControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: SetupWeightGoalController.url(options),
    method: 'post',
})

SetupWeightGoalController.form = SetupWeightGoalControllerForm

export default SetupWeightGoalController