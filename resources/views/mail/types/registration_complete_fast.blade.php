@extends('mail.default')

@section('mail-body')
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td>
                <h3 style="text-align: center; color: white">Вас приветсвтует команда ALPHA CHEAT</h3>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 0 30px 0;">
                <p style="color: white">Вы успешно зарегестрировались на <a href="{{url('/')}}" target="_blank" style="text-decoration: none; color: #ee166c">сайте</a>.</p>
                <p style="color: white">Для подтверждения Вашей учетной записи, перейдите по следующей <a href="{{$link}}" target="_blank" style="text-decoration: none; color: #ee166c; text-transform: uppercase;">ссылке</a>.</p>
            </td>
        </tr>
        <tr>
            <td>
                <h4 style="text-align: center; color: white">Ваши данные от аккаунта</h4>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%">
                    <tr style="color:  white; font-size: 20px">
                        <td style="color: white"><b style="color: white">Пароль: </b></td>
                        <td><b style="color: #EE166C">{{@$password}}</b></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
