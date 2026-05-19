import PasswordController from './PasswordController'
import ProfileController from './ProfileController'
import SecurityController from './SecurityController'
import WeightGoalController from './WeightGoalController'

const Settings = {
    PasswordController: Object.assign(PasswordController, PasswordController),
    ProfileController: Object.assign(ProfileController, ProfileController),
    SecurityController: Object.assign(SecurityController, SecurityController),
    WeightGoalController: Object.assign(WeightGoalController, WeightGoalController),
}

export default Settings