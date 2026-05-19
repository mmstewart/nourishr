import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ContactController::__invoke
* @see app/Http/Controllers/ContactController.php:12
* @route '/contact'
*/
const ContactController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ContactController.url(options),
    method: 'post',
})

ContactController.definition = {
    methods: ["post"],
    url: '/contact',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ContactController::__invoke
* @see app/Http/Controllers/ContactController.php:12
* @route '/contact'
*/
ContactController.url = (options?: RouteQueryOptions) => {
    return ContactController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ContactController::__invoke
* @see app/Http/Controllers/ContactController.php:12
* @route '/contact'
*/
ContactController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ContactController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ContactController::__invoke
* @see app/Http/Controllers/ContactController.php:12
* @route '/contact'
*/
const ContactControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: ContactController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ContactController::__invoke
* @see app/Http/Controllers/ContactController.php:12
* @route '/contact'
*/
ContactControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: ContactController.url(options),
    method: 'post',
})

ContactController.form = ContactControllerForm

export default ContactController