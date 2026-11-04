// <script setup>
// import { Head, useForm, router, Link } from '@inertiajs/vue3';
// import Toast from '@/Components/Toast.vue';
// import TextInput from '@/Components/TextInput.vue';
// import InputError from '@/Components/InputError.vue';
// import PrimaryButton from '@/Components/PrimaryButton.vue';
// import Search from '@/Components/Search.vue';
// import Modal2 from '@/Components/Modal2.vue';


// const props = defineProps({
//     accounts: {
//         type: Array,
//     },
//     products: {
//         type: Array,
//     },
//     categories: {
//         type: Array,
//     },
//     flash: {
//         type: Object,
//         required: true,
//     },
// });

// const form = useForm({
//     sale_day: new Date().toLocaleDateString('en-CA'),
//     cust_id: '1',
//     order_type: 'Dine_In',
//     table_no: '1',
//     gross: '0',
//     received: '',
//     remaining: '0',
//     return: '0',
//     pros: [],

// });

// // const submit = () => {
// //     if (form.remaining == 0) {
// //         form.post(route('saveInvoice2'), {
// //             onSuccess: showPrintWin(),
// //         });
// //     }
// //     else {
// //         alert("Remaining Amount must be equals to '0'.");
// //     }

// // };
// const submit = async () => {
//     if (form.remaining === 0) {
//         try {
//             await form.post(route('saveInvoice2'));
//             showPrintWin();
//         } catch (error) {
//             console.error('Error while posting data:', error);
//         }
//     } else {
//         alert("Remaining Amount must be equal to '0'.");
//     }
// };

// const getProduct = (e) => {
//     router.get('saleForm2', { cat_id: e }, { preserveState: true, replace: true });

//     setFocus();
// }

// const addNewRow = (id, name, price, index, item_qty) => {

//     const existingIndex = form.pros.findIndex(item => item.id === id);
//     if (existingIndex !== -1) {
//         const updatedItem = {
//             'id': id,
//             'name': name,
//             'price': price,
//             'qty': Number(form.pros[existingIndex].qty) + 1,
//             'item_total': (Number(form.pros[existingIndex].qty) + 1) * Number(price),
//         }
//         form.pros.splice(existingIndex, 1, updatedItem);

//     } else {

//         form.pros.push({
//             'id': id,
//             'name': name,
//             'price': price,
//             'qty': 1,
//             'item_total': Number(1) * Number(price),
//         });

//     }

//     grosss();

//     setFocus();
//     calculate();
// }

// const grosss = () => {

//     let gross2 = 0;
//     for (const item of form.pros) {
//         gross2 += item.item_total;
//     }
//     form.gross = gross2;

// }

// const removeRow = (index) => {
//     form.pros.splice(index, 1);

//     setFocus();
//     calculate();
// }

// const clearRow = () => {

//     form.pros.splice(0);
//     clearField();
//     setFocus();
// }

// const clearField = () => {
//     form.gross = 0;
//     form.received = '';
//     form.remaining = 0;
//     form.return = 0;
// }

// const calculate = () => {

//     grosss();
//     let gross3 = form.gross;
//     let received2 = form.received;
//     let cal = Number(gross3) - Number(received2);

//     if (cal <= 0) {
//         form.remaining = 0;
//     }
//     else {
//         form.remaining = cal;
//     }

//     if (received2 > 0) {
//         form.return = Math.abs(cal);
//     }
//     else {
//         form.return = 0;
//     }

//     setFocus();
// }

// const setFocus = () => {
//     const element = document.getElementById('received');
//     if (element) {
//         element.focus();
//     }
// }

// let delayTimeout;
// const showPrintWin = () => {

//     delayTimeout = setTimeout(() => {
//         window.open('/salePrint/' + props.flash.message, 'popUpWindow', 'height=400,width=1000,left=50,top=50,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
//         clearRow();
//     }, 3500);


// }

// const incrementQuantity = () => {

//     const input = document.getElementById('quantityInput');
//     input.stepUp();

// }

// const decrementQuantity = () => {

//     const input = document.getElementById('quantityInput');
//     input.stepDown();
// }

// const openModal = (id,name,price) => {

//     document.getElementById('item_name').innerHTML = name;
//     document.getElementById('item_id').value = id;
//     document.getElementById('item_name2').value = name;
//     document.getElementById('item_price').value = price;
//     $('#modal_qty').modal('toggle');

// }

// const updateQty = () => {

//     const input2 = document.getElementById('quantityInput').value;
//     const id = document.getElementById('item_id').value;
//     const name = document.getElementById('item_name2').value;
//     const price = document.getElementById('item_price').value;
//     const existingIndex = form.pros.findIndex(item => item.id === id);
//     if (existingIndex !== -1) {
//         const updatedItem = {
//             'id': id,
//             'name': name,
//             'price': price,
//             'qty': input2,
//             'item_total': (Number(input2)) * Number(price),
//         }
//         form.pros.splice(existingIndex, 1, updatedItem);

//     }

//     grosss();

//     setFocus();
//     calculate();
//     document.getElementById('quantityInput').value = 1;
//     $('#modal_qty').modal('toggle');

// }

// </script>
// <style>
// .borderTable {
//     border: 1px solid black;
// }
// </style>
// <template>

//     <Head title="Sale" />

//     <div class="row vh-100">
        

//         <div class="bg-gray-100 col-md-9">
//             <div class="container-fluid">
//                 <!-- Page Heading -->
//                 <div class="align-items-center justify-content-between mt-2">
//                     <div class="">
                       
//                         <div class="row">
//                             <!-- <div class="col-md-2">
//                                 <h1 class="mb-0 text-gray-800 text-sm">Sale Menu</h1>
//                                 <Link :href="route('inventoryCheck')" class="btn btn-primary mr-2">
//                                     Back
//                                 </Link>
//                             </div> -->
//                             <div class="col-md-2">
//                                 <Search :txt="'/saleForm2'" placholder="Search..." />
//                             </div>
//                             <div class="col-md-3">
//                                 <label for="category" class="block text-sm">Category:</label>
//                                 <select id="category" class="form-control" @change="getProduct($event.target.value)">
//                                     <option v-for="category in categories" :key="category.id" :value="category.id">
//                                         {{ category.name }}
//                                     </option>
//                                 </select>
//                             </div>
                            
//                         </div>
//                     </div>
                    
//                 </div>

//                 <div class="row">
//                     <div class="col-sm-4 col-md-4 col-lg-2 mt-2" v-for="product, index in products" :key="product.id">
//                         <div class="card shadow-md h-100">
//                             <div class="card-body text-center">
//                                 <a href="#" @click="addNewRow(product.barcode, product.name, product.retail, index)">
//                                     <!-- <i class="fas fa-calendar fa-2x text-gray-300"></i> -->
//                                     <img width="48" height="48" class="ml-3" src="https://img.icons8.com/color/48/meal--v1.png" alt="hamburger" />
//                                     <p class="text-black text-sm">{{ product.name }}</p>
//                                 </a>
//                             </div>
//                         </div>
//                     </div>
//                 </div>

//                 <!-- <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" /> -->
//             </div>
//         </div>

//         <div class="bg-green-100 pl-1 pr-1 col-md-3">
//             <form @submit.prevent="submit">
//                 <table>
//                     <tr>
//                         <td>
//                             Date: {{ new Date().toLocaleDateString('en-GB') }}
//                             <TextInput id="sale_day" type="date" class="mt-1 block w-full" v-model="form.sale_day"
//                                 required autofocus autocomplete="name" placeholder="Enter Date" v-show="false" />
//                         </td>
//                         <td>
//                             <span class="text-sm">Sale Menu</span>
//                         </td>
//                     </tr>
//                     <tr>
//                         <td>
//                             <span class="text-sm">Order Type:</span>
//                             <select class="mt-1 block w-full border-gray-300 focus:border-indigo-500 
//                                         :ring-indigo-500 rounded-md shadow-sm text-sm w-[150px]" style="width: 200px"
//                                 id="order_type" v-model="form.order_type" required>
//                                 <option value="Dine_In">Dine In</option>
//                                 <option value="Take_Away">Take away</option>
//                                 <option value="Delivery">Delivery</option>
//                             </select>

//                             <InputError class="mt-2" :message="form.errors.order_type" />
//                         </td>
//                         <td>
//                             <span class="text-sm">Table:</span>
//                             <select class="mt-1 block w-full border-gray-300 focus:border-indigo-500 
//                                         :ring-indigo-500 rounded-md shadow-sm text-sm" id="order_type"
//                                 v-model="form.table_no" required>
//                                 <option value="1">1</option>
//                                 <option value="2">2</option>
//                                 <option value="3">3</option>
//                                 <option value="4">4</option>
//                                 <option value="5">5</option>
//                                 <option value="6">6</option>
//                                 <option value="7">7</option>
//                                 <option value="8">8</option>
//                                 <option value="9">9</option>
//                                 <option value="10">10</option>
//                                 <option value="11">11</option>
//                                 <option value="12">12</option>
//                                 <option value="13">13</option>
//                                 <option value="14">14</option>
//                                 <option value="15">15</option>
//                             </select>

//                             <InputError class="mt-2" :message="form.errors.table_no" />
//                         </td>

//                     </tr>
//                     <tr>
//                         <td>
//                             <span class="text-sm">Customer Name:</span>
//                             <select class="mt-1 block w-full border-gray-300 focus:border-indigo-500 
//                                         :ring-indigo-500 rounded-md shadow-sm text-sm w-[150px]" style="width: 200px"
//                                 id="cust_id" v-model="form.cust_id" required>
//                                 <option disabled value="0">-- Select --</option>
//                                 <option v-for="account in accounts" :value="account.id">
//                                     {{ account.name }}
//                                 </option>
//                             </select>

//                             <InputError class="mt-2" :message="form.errors.cust_id" />
//                         </td>
//                     </tr>
//                 </table>
//                 <div class="overflow-y-auto h-[300px] bg-white">
//                     <table class="text-sm " width="100%">
//                         <thead>
//                             <tr class="borderTable">
//                                 <td>Name</td>
//                                 <td>Particulars</td>
//                                 <td>.</td>
//                             </tr>
//                         </thead>
//                         <tbody>
//                             <tr v-for="pro, index in form.pros" :key="index">
//                                 <td>{{ pro.name }}</td>
//                                 <td><a href="#" @click="openModal(pro.id, pro.name, pro.price)">{{ pro.qty + ' x ' +
//                                         pro.price + ': ' + pro.item_total }}</a></td>
//                                 <td><a href="#" @click="removeRow(index)" class="flex justify-center"><i
//                                             class="fas fa-trash text-red-600"></i></a></td>
//                             </tr>
//                         </tbody>
//                     </table>
//                 </div>
//                 <table>
//                     <tr>
//                         <td>Gross:</td>
//                         <td class="text-black text-md">{{ form.gross }}</td>
//                     </tr>
//                     <tr>
//                         <td>Received:</td>
//                         <td>
//                             <TextInput id="received" type="number" class="mt-1 block w-full text-sm"
//                                 v-model="form.received" required autofocus autocomplete="received"
//                                 placeholder="Enter Received" @keyup="calculate" />
//                             <InputError class="mt-2" :message="form.errors.received" />
//                         </td>
//                     </tr>
//                     <tr>
//                         <td>Remaining:</td>
//                         <td class="text-black text-md">{{ form.remaining }}</td>
//                     </tr>
//                     <tr>
//                         <td>Return:</td>
//                         <td class="text-black text-md">{{ form.return }}</td>
//                     </tr>
//                     <tr>
//                         <td>
//                             <PrimaryButton @click.prevent="clearRow" class=" btn btn-primary">
//                                 Cancel
//                             </PrimaryButton>
//                         </td>
//                         <td>
//                             <PrimaryButton class="btn btn-primary">
//                                 Save
//                             </PrimaryButton>
//                         </td>
//                     </tr>
//                 </table>
//             </form>
//         </div>

//         <!-- <div class="flex-2 overflow-y-auto bg-yellow-100 w-[130px]">
//             <h6 class="mb-0 mt-1 text-gray-800 text-center">Category</h6>
//             <div class="row p-1">
//                 <div class="col-sm-12 col-md-12 col-lg-12 mt-1" v-for="category in categories" :key="category.id">
//                     <div class="card shadow-md h-100">
//                         <div class="card-body text-center">
//                             <a href="#" @click="getProduct(category.id)"> -->
//                                 <!-- <i class="fas fa-calendar fa-2x text-gray-300"></i> -->
//                                 <!-- <img width="48" height="48" class="ml-2" :src="category.image_uri" alt="Icon" />
//                                 <p class="text-black text-sm">{{ category.name }}</p>
//                             </a>
//                         </div>
//                     </div>
//                 </div>
//             </div>
//         </div> -->
//     </div>

//     <Modal2 id="modal_qty" title="Enter Qty">
//         <table class="table">
//             <tr>
//                 <td>
//                     <h1 id="item_name"></h1>
//                     <input id="item_id" hidden/>
//                     <input id="item_name2" hidden/>
//                     <input id="item_price" hidden/>
//                 </td>
//             </tr>
//             <tr>
//                 <td>
//                     <div class="input-group">
//                         <div class="input-group-append">
//                             <button class="btn btn-outline-secondary" type="button"
//                                 @click="decrementQuantity()">-</button>
//                             <input type="number" id="quantityInput" class="form-control text-center" value="1" min="1">
//                             <button class="btn btn-outline-secondary" type="button"
//                                 @click="incrementQuantity()">+</button>
//                         </div>
//                     </div>
//                 </td>
//             </tr>
//             <tr>
//                 <td>
//                     <PrimaryButton @click.prevent="updateQty" class="btn btn-primary">
//                         Update
//                     </PrimaryButton>
//                 </td>
//             </tr>
//         </table>


//     </Modal2>



// </template>
