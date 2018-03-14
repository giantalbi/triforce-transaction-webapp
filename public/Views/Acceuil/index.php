<div class='col-sm-6 col-11 justify-content-between'>
    <button type="button" class="btn btn-info col-12" data-toggle="modal" data-target="#addProductModal">
    Ajouter un produit
    </button>
    <hr>
    <ul class='list-group' id='products'>
        <?php
        foreach($data['products'] as $product){
        ?>
            <li data-id='<?=$product->ProductID?>' data-price='<?=$product->Price?>' class='list-group-item clearfix d-flex'>
            <span class='p-2'><?=$product->Name?></span>
            <b class='p-2'><?=$product->Price?>$</b>
            <span class='ml-auto'>
                <span class="btn btn-outline-secondary remove-product" type="button">-</span>
                <input class='count' type='text' min='0' maxlength='2' size='2' value='0' readonly/>
                <span class="btn btn-outline-secondary add-product" type="button">+</span>
            </span>
        </li>
        <?php } ?>
    </ul>
    <hr class='invisible'>
    <!-- Button trigger modal -->
    <div class='row col-12'>
        <b>Total: <span id='total'>0.00$</span></b>
    </div>
    <button type="button" class="btn btn-success col-12" data-toggle="modal" data-target="#transactionModal">
    Envoyer
    </button>
</div>

<!-- Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Envoyer la transaction ?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
        <button onclick='sendTransaction()' id='sendTransaction' type="button" class="btn btn-primary">Oui</button> </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Ajouter un produit</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
            <div class='form-group'>
                <input class='form-control' id='name' type='text' placeholder='Nom' required/>
                <br>
                <input class='form-control' id='price' type='number' min='0.00' max='9999.00' step='0.01' placeholder='Prix' required/>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button onclick='createProduct()' type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<script>

function createProduct(){
    $.ajax({
        method: 'POST',
        url: '/api/Product/create/',
        data: {Name: $('#name').val(), Price: $('#price').val()},
        success: () => {
            window.location.reload();
        },
        error: (error) => {
            //TODO: Put alert inside modal of responseText != ""
            console.log(error.responseText)
        }
    });
}

function sendTransaction(){
    var products = {};
    $('#products li').each((i, v) => {
        var qty = parseInt($('input', v).val());
        if(qty > 0)
            products[$(v).data('id')] = qty;
    });
    $.ajax({
        method: 'POST',
        url: '/api/Transaction/create/',
        data: {total: getTotal(), products: products},
        success: (data) => {
            window.location.reload();
        },
        error: (err) => {
            console.log(err.responseText);
        }
    });
}

function getTotal(){
    var total = 0;
    $('#products li').each((i, v) => {
        total += parseFloat($(v).data('price')) * parseInt($('input', v).val())
    });
    return total;
}

function setTotal(){
    var totalFormat = getTotal().toLocaleString('fr-CA', { style: 'currency', currency: 'CAD' });
    $('#total').html(totalFormat);
}

$(() => {
    $('.remove-product').on('click', (e) => {
        e.preventDefault();    
        var input = $("input", $(e.target).parent());
        if(parseInt(input.val()) > 0)
            input.val(parseInt(input.val()) - 1)
        setTotal();
    });   

    $('.add-product').on('click', (e) => {
        e.preventDefault();    
        var input = $("input", $(e.target).parent());
        input.val(parseInt(input.val()) + 1);
        setTotal();
    });    
});

</script>
