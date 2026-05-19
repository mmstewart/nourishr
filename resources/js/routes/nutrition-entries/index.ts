import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\NutritionEntryController::index
* @see app/Http/Controllers/NutritionEntryController.php:13
* @route '/nutrition-entries'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/nutrition-entries',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NutritionEntryController::index
* @see app/Http/Controllers/NutritionEntryController.php:13
* @route '/nutrition-entries'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NutritionEntryController::index
* @see app/Http/Controllers/NutritionEntryController.php:13
* @route '/nutrition-entries'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::index
* @see app/Http/Controllers/NutritionEntryController.php:13
* @route '/nutrition-entries'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::index
* @see app/Http/Controllers/NutritionEntryController.php:13
* @route '/nutrition-entries'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::index
* @see app/Http/Controllers/NutritionEntryController.php:13
* @route '/nutrition-entries'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::index
* @see app/Http/Controllers/NutritionEntryController.php:13
* @route '/nutrition-entries'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\NutritionEntryController::create
* @see app/Http/Controllers/NutritionEntryController.php:99
* @route '/nutrition-entries/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/nutrition-entries/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NutritionEntryController::create
* @see app/Http/Controllers/NutritionEntryController.php:99
* @route '/nutrition-entries/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NutritionEntryController::create
* @see app/Http/Controllers/NutritionEntryController.php:99
* @route '/nutrition-entries/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::create
* @see app/Http/Controllers/NutritionEntryController.php:99
* @route '/nutrition-entries/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::create
* @see app/Http/Controllers/NutritionEntryController.php:99
* @route '/nutrition-entries/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::create
* @see app/Http/Controllers/NutritionEntryController.php:99
* @route '/nutrition-entries/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::create
* @see app/Http/Controllers/NutritionEntryController.php:99
* @route '/nutrition-entries/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\NutritionEntryController::store
* @see app/Http/Controllers/NutritionEntryController.php:106
* @route '/nutrition-entries'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/nutrition-entries',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\NutritionEntryController::store
* @see app/Http/Controllers/NutritionEntryController.php:106
* @route '/nutrition-entries'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NutritionEntryController::store
* @see app/Http/Controllers/NutritionEntryController.php:106
* @route '/nutrition-entries'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::store
* @see app/Http/Controllers/NutritionEntryController.php:106
* @route '/nutrition-entries'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::store
* @see app/Http/Controllers/NutritionEntryController.php:106
* @route '/nutrition-entries'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\NutritionEntryController::show
* @see app/Http/Controllers/NutritionEntryController.php:127
* @route '/nutrition-entries/{nutrition_entry}'
*/
export const show = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/nutrition-entries/{nutrition_entry}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NutritionEntryController::show
* @see app/Http/Controllers/NutritionEntryController.php:127
* @route '/nutrition-entries/{nutrition_entry}'
*/
show.url = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { nutrition_entry: args }
    }

    if (Array.isArray(args)) {
        args = {
            nutrition_entry: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        nutrition_entry: args.nutrition_entry,
    }

    return show.definition.url
            .replace('{nutrition_entry}', parsedArgs.nutrition_entry.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\NutritionEntryController::show
* @see app/Http/Controllers/NutritionEntryController.php:127
* @route '/nutrition-entries/{nutrition_entry}'
*/
show.get = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::show
* @see app/Http/Controllers/NutritionEntryController.php:127
* @route '/nutrition-entries/{nutrition_entry}'
*/
show.head = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::show
* @see app/Http/Controllers/NutritionEntryController.php:127
* @route '/nutrition-entries/{nutrition_entry}'
*/
const showForm = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::show
* @see app/Http/Controllers/NutritionEntryController.php:127
* @route '/nutrition-entries/{nutrition_entry}'
*/
showForm.get = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::show
* @see app/Http/Controllers/NutritionEntryController.php:127
* @route '/nutrition-entries/{nutrition_entry}'
*/
showForm.head = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\NutritionEntryController::edit
* @see app/Http/Controllers/NutritionEntryController.php:150
* @route '/nutrition-entries/{nutrition_entry}/edit'
*/
export const edit = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/nutrition-entries/{nutrition_entry}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NutritionEntryController::edit
* @see app/Http/Controllers/NutritionEntryController.php:150
* @route '/nutrition-entries/{nutrition_entry}/edit'
*/
edit.url = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { nutrition_entry: args }
    }

    if (Array.isArray(args)) {
        args = {
            nutrition_entry: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        nutrition_entry: args.nutrition_entry,
    }

    return edit.definition.url
            .replace('{nutrition_entry}', parsedArgs.nutrition_entry.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\NutritionEntryController::edit
* @see app/Http/Controllers/NutritionEntryController.php:150
* @route '/nutrition-entries/{nutrition_entry}/edit'
*/
edit.get = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::edit
* @see app/Http/Controllers/NutritionEntryController.php:150
* @route '/nutrition-entries/{nutrition_entry}/edit'
*/
edit.head = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::edit
* @see app/Http/Controllers/NutritionEntryController.php:150
* @route '/nutrition-entries/{nutrition_entry}/edit'
*/
const editForm = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::edit
* @see app/Http/Controllers/NutritionEntryController.php:150
* @route '/nutrition-entries/{nutrition_entry}/edit'
*/
editForm.get = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::edit
* @see app/Http/Controllers/NutritionEntryController.php:150
* @route '/nutrition-entries/{nutrition_entry}/edit'
*/
editForm.head = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\NutritionEntryController::update
* @see app/Http/Controllers/NutritionEntryController.php:162
* @route '/nutrition-entries/{nutrition_entry}'
*/
export const update = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/nutrition-entries/{nutrition_entry}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\NutritionEntryController::update
* @see app/Http/Controllers/NutritionEntryController.php:162
* @route '/nutrition-entries/{nutrition_entry}'
*/
update.url = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { nutrition_entry: args }
    }

    if (Array.isArray(args)) {
        args = {
            nutrition_entry: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        nutrition_entry: args.nutrition_entry,
    }

    return update.definition.url
            .replace('{nutrition_entry}', parsedArgs.nutrition_entry.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\NutritionEntryController::update
* @see app/Http/Controllers/NutritionEntryController.php:162
* @route '/nutrition-entries/{nutrition_entry}'
*/
update.put = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::update
* @see app/Http/Controllers/NutritionEntryController.php:162
* @route '/nutrition-entries/{nutrition_entry}'
*/
update.patch = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::update
* @see app/Http/Controllers/NutritionEntryController.php:162
* @route '/nutrition-entries/{nutrition_entry}'
*/
const updateForm = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::update
* @see app/Http/Controllers/NutritionEntryController.php:162
* @route '/nutrition-entries/{nutrition_entry}'
*/
updateForm.put = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::update
* @see app/Http/Controllers/NutritionEntryController.php:162
* @route '/nutrition-entries/{nutrition_entry}'
*/
updateForm.patch = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\NutritionEntryController::destroy
* @see app/Http/Controllers/NutritionEntryController.php:173
* @route '/nutrition-entries/{nutrition_entry}'
*/
export const destroy = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/nutrition-entries/{nutrition_entry}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\NutritionEntryController::destroy
* @see app/Http/Controllers/NutritionEntryController.php:173
* @route '/nutrition-entries/{nutrition_entry}'
*/
destroy.url = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { nutrition_entry: args }
    }

    if (Array.isArray(args)) {
        args = {
            nutrition_entry: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        nutrition_entry: args.nutrition_entry,
    }

    return destroy.definition.url
            .replace('{nutrition_entry}', parsedArgs.nutrition_entry.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\NutritionEntryController::destroy
* @see app/Http/Controllers/NutritionEntryController.php:173
* @route '/nutrition-entries/{nutrition_entry}'
*/
destroy.delete = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::destroy
* @see app/Http/Controllers/NutritionEntryController.php:173
* @route '/nutrition-entries/{nutrition_entry}'
*/
const destroyForm = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NutritionEntryController::destroy
* @see app/Http/Controllers/NutritionEntryController.php:173
* @route '/nutrition-entries/{nutrition_entry}'
*/
destroyForm.delete = (args: { nutrition_entry: string | number } | [nutrition_entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const nutritionEntries = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default nutritionEntries