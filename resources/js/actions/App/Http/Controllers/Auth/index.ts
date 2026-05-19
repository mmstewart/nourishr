import LoginController from './LoginController'
import RegisterController from './RegisterController'
import GoogleAuthCallbackController from './GoogleAuthCallbackController'
import GoogleAuthRedirectController from './GoogleAuthRedirectController'
import SetupController from './SetupController'
import SetupAccountController from './SetupAccountController'
import SetupProfileController from './SetupProfileController'
import SetupWeightGoalController from './SetupWeightGoalController'

const Auth = {
    LoginController: Object.assign(LoginController, LoginController),
    RegisterController: Object.assign(RegisterController, RegisterController),
    GoogleAuthCallbackController: Object.assign(GoogleAuthCallbackController, GoogleAuthCallbackController),
    GoogleAuthRedirectController: Object.assign(GoogleAuthRedirectController, GoogleAuthRedirectController),
    SetupController: Object.assign(SetupController, SetupController),
    SetupAccountController: Object.assign(SetupAccountController, SetupAccountController),
    SetupProfileController: Object.assign(SetupProfileController, SetupProfileController),
    SetupWeightGoalController: Object.assign(SetupWeightGoalController, SetupWeightGoalController),
}

export default Auth