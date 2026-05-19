import Auth from './Auth'
import ContactController from './ContactController'
import DashboardController from './DashboardController'
import FeedbackController from './FeedbackController'
import NutritionEntryController from './NutritionEntryController'
import WeightEntryController from './WeightEntryController'
import Settings from './Settings'

const Controllers = {
    Auth: Object.assign(Auth, Auth),
    ContactController: Object.assign(ContactController, ContactController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    FeedbackController: Object.assign(FeedbackController, FeedbackController),
    NutritionEntryController: Object.assign(NutritionEntryController, NutritionEntryController),
    WeightEntryController: Object.assign(WeightEntryController, WeightEntryController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers