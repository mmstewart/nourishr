import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\FeedbackController::__invoke
* @see app/Http/Controllers/FeedbackController.php:12
* @route '/feedback'
*/
const FeedbackController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: FeedbackController.url(options),
    method: 'post',
})

FeedbackController.definition = {
    methods: ["post"],
    url: '/feedback',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FeedbackController::__invoke
* @see app/Http/Controllers/FeedbackController.php:12
* @route '/feedback'
*/
FeedbackController.url = (options?: RouteQueryOptions) => {
    return FeedbackController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FeedbackController::__invoke
* @see app/Http/Controllers/FeedbackController.php:12
* @route '/feedback'
*/
FeedbackController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: FeedbackController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FeedbackController::__invoke
* @see app/Http/Controllers/FeedbackController.php:12
* @route '/feedback'
*/
const FeedbackControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: FeedbackController.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FeedbackController::__invoke
* @see app/Http/Controllers/FeedbackController.php:12
* @route '/feedback'
*/
FeedbackControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: FeedbackController.url(options),
    method: 'post',
})

FeedbackController.form = FeedbackControllerForm

export default FeedbackController