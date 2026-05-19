import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
export const account = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: account.url(options),
    method: 'post',
})

account.definition = {
    methods: ["post"],
    url: '/setup-account',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
account.url = (options?: RouteQueryOptions) => {
    return account.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
account.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: account.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
const accountForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: account.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupAccountController::__invoke
* @see app/Http/Controllers/Auth/SetupAccountController.php:12
* @route '/setup-account'
*/
accountForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: account.url(options),
    method: 'post',
})

account.form = accountForm

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
export const profile = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: profile.url(options),
    method: 'post',
})

profile.definition = {
    methods: ["post"],
    url: '/setup-profile',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
profile.url = (options?: RouteQueryOptions) => {
    return profile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
profile.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: profile.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
const profileForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: profile.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupProfileController::__invoke
* @see app/Http/Controllers/Auth/SetupProfileController.php:10
* @route '/setup-profile'
*/
profileForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: profile.url(options),
    method: 'post',
})

profile.form = profileForm

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
export const weightGoal = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: weightGoal.url(options),
    method: 'post',
})

weightGoal.definition = {
    methods: ["post"],
    url: '/setup-weight-goal',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
weightGoal.url = (options?: RouteQueryOptions) => {
    return weightGoal.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
weightGoal.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: weightGoal.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
const weightGoalForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: weightGoal.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\SetupWeightGoalController::__invoke
* @see app/Http/Controllers/Auth/SetupWeightGoalController.php:11
* @route '/setup-weight-goal'
*/
weightGoalForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: weightGoal.url(options),
    method: 'post',
})

weightGoal.form = weightGoalForm

const setup = {
    account: Object.assign(account, account),
    profile: Object.assign(profile, profile),
    weightGoal: Object.assign(weightGoal, weightGoal),
}

export default setup