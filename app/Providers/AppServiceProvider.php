<?php

namespace App\Providers;

use App\Models\ApiUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Http\Response;
use Kreait\Firebase\Database;
use Illuminate\Validation\Rules;
use Kreait\Firebase\ServiceAccount;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Support\ServiceProvider;
use Firebase\Firestore\FirebaseFirestore;
use Illuminate\Support\Facades\Validator;
use Google\Cloud\Firestore\DocumentSnapshot;
use Kreait\Firebase\Auth\SignIn\PhoneNumber;
use Kreait\Firebase\Auth\SignIn\PhoneSignInResult;

use Kreait\Firebase\Contract\Auth as AuthFirebase;
use Kreait\Firebase\Messaging\WebPushNotification;
use Kreait\Firebase\Exception\Auth\PhoneNumberExists;
use Kreait\Firebase\Exception\Auth\InvalidPhoneNumber;
use Kreait\Firebase\Exception\Auth\PhoneNumberAlreadyExists;
use App\Doctrine\EnumType;
use Doctrine\DBAL\Types\Type;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Type::addType('enum', EnumType::class);
    }
    
    

}
