import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\WeightGoalController::edit
* @see app/Http/Controllers/Settings/WeightGoalController.php:13
* @route '/settings/weight-goal'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/weight-goal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::edit
* @see app/Http/Controllers/Settings/WeightGoalController.php:13
* @route '/settings/weight-goal'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::edit
* @see app/Http/Controllers/Settings/WeightGoalController.php:13
* @route '/settings/weight-goal'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::edit
* @see app/Http/Controllers/Settings/WeightGoalController.php:13
* @route '/settings/weight-goal'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::edit
* @see app/Http/Controllers/Settings/WeightGoalController.php:13
* @route '/settings/weight-goal'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::edit
* @see app/Http/Controllers/Settings/WeightGoalController.php:13
* @route '/settings/weight-goal'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::edit
* @see app/Http/Controllers/Settings/WeightGoalController.php:13
* @route '/settings/weight-goal'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::update
* @see app/Http/Controllers/Settings/WeightGoalController.php:28
* @route '/settings/weight-goal'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/settings/weight-goal',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::update
* @see app/Http/Controllers/Settings/WeightGoalController.php:28
* @route '/settings/weight-goal'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::update
* @see app/Http/Controllers/Settings/WeightGoalController.php:28
* @route '/settings/weight-goal'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::update
* @see app/Http/Controllers/Settings/WeightGoalController.php:28
* @route '/settings/weight-goal'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\WeightGoalController::update
* @see app/Http/Controllers/Settings/WeightGoalController.php:28
* @route '/settings/weight-goal'
*/
updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const weightGoal = {
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
}

export default weightGoal