<div class="ui fullscreen modal" id="products-modal">
    <i class="close icon"></i>
    <div class="header">Оплата подписки</div>
    <form class="ui form" style="padding: 30px;" id="products-form">
        <div id="products-context">
            <div class="ui stackable secondary menu transparent categories" style="overflow: auto; min-width: 500px">
                @for($i = 0; $i < count(@$products); $i++)
                    <div class="item @if(@$i == 0)  active @endif " data-tab="t-{{@$i}}">{{@$products[$i]['title']}}</div>
                @endfor
            </div>
            @for($i = 0; $i < count(@$products); $i++)
                <div class="ui tab {{@$i == 0 ? 'active' : ''}}" data-tab="t-{{@$i}}">
                    <br>
                    <div style="padding: 0 27px">
                        <h2>Содержит компоненты</h2>
                        <ol class="ui list" style="font-size: 16px">
                            @foreach(@$products[$i]['modules'] as $module)
                                <li class="item" value="•">{{@$module->name}} ({{@$module->description}})</li>
                            @endforeach
                        </ol>
                    </div>
                    <br>
                    <div class="ui vertical menu products fluid" style="background: transparent; padding: 0 12px">
                        @foreach(@$products[$i]['costs'] as $cost)
                            <div class="item" data-cost="{{@$cost['cid']}}" data-product="{{@$products[@$i]['id']}}" data-cost-val="{{@$cost['cost'][0]}}">
                                {{@$cost['increment']->title}}
                            </div>
                        @endforeach
                    </div>
                    <br>
                </div>
            @endfor
        </div>
        <div id="total-cost" style="font-size: 30px; padding: 0 32px; display: none;">
            К оплате: <span class="ui teal label" style="font-size: 30px" id="total-cost-val"></span>
        </div>
        <input id="cost-id" name="cid" type="hidden" required>
        <input id="product-id" name="pid" type="hidden" required>
        <br>
        <div class="field">
            <button class="ui alpha button fluid" type="button" onclick="purchase()">Купить для себя</button>
        </div>
        <div class="field">
            <button class="ui alpha light button fluid" type="button" onclick="use_promo()">Купить в подарок</button>
        </div>
    </form>
</div>
