<?php
namespace App\Plugins\Authorize;

use App\Http\Requests\Request;

class Authorize
{

    /*
     * 指定当前目录下 显示此数据的视图
     * */
    public $dataSourceView = 'List';
    public $dataSource     = 'getList';

    public function run( $name = null )
    {
        $type = Request()->input( 'type' );

        if ( $type == 'login' )
            return $this->login();
        else if ( $type == 'register' )
            return $this->register();
        else if ( $type == 'logout' )
            return $this->logout();
    }

    public function login()
    {
        $username = Request()->input( 'username' );
        $password = Request()->input( 'password' );

        $login = \Auth( 'web' )->attempt( [
            'username' => $username,
            'password' => $password,
        ] );

        if ( $login ) {

            return Response()->json( [
                'code'    => 'success',
                'message' => '登录成功!',
                'auth'    => Auth( 'admin' )->user()
            ] );
        } else {
            return Response()->json( [
                'code'    => 'error',
                'message' => '登录失败!',
            ] );
        }
    }

    public function register()
    {
        $username = Request()->input( 'username' );
        $password = Request()->input( 'password' );
        $email    = Request()->input( 'email' );
        $nickname = Request()->input( 'nickname' );
        $phone    = Request()->input( 'phone' );

        $this->validate( Request(), [
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'email'    => 'required|unique:users|email',
            'phone'    => 'required',
        ], [
            'required' => '必填写内容',
            'unique'   => '信息已经存在了',
        ] );

        $user           = new \App\User;
        $user->username = $username;
        $user->password = \Hash::make( $password );
        $user->email    = $email;
        $user->nickname = $nickname;
        $user->phone    = $phone;

        if ( $user->save() ) {
            Auth( 'web' )->login( $user, true );

            return Response()->json( [
                'code'    => 'success',
                'message' => '注册成功!'
            ] );
        } else {
            return Response()->json( [
                'code'    => 'error',
                'message' => '注册失败!',
            ] );
        }
    }

    public function logout()
    {

    }

    public function getList()
    {
        $users = \App\User::all();

        return \Plugins::view( class_basename( __CLASS__ ), $this->dataSourceView )->with( [
            'users' => $users
        ] );
    }

    public function getListJson()
    {
        $forms = \App\Form::wherePlugin( lcfirst( class_basename( __CLASS__ ) ) )->get();

        return \Plugins::view( class_basename( __CLASS__ ), $this->dataSourceView )->with( [
            'forms' => $forms
        ] );
    }

}