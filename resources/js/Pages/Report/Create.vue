<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';


const props = defineProps ({
    from: {
        type: String,
    }
});

const form = useForm({
    from_: new Date().toISOString().substr(0, 10),
    to: new Date().toISOString().substr(0, 10),
});

const submit = () => {
    if(props.from == 'Cashin'){
        form.get(route('getCashinReport'), {});
    }
    else if(props.from == 'Cashout'){
        form.get(route('getCashoutReport'), {});
    }
    else if(props.from == 'Receivable'){
        form.get(route('getReceivableReport'), {});
    }
    else if(props.from == 'Payable'){
        form.get(route('getPayableReport'), {});
    }
    else if(props.from == 'Stock'){
        form.get(route('getStockReport'), {});
    }
    else if(props.from == 'StockTracking'){
        form.get(route('getStockTrackReport'), {});
    }
    else if(props.from == 'DayEnd'){
        form.get(route('getDayendReport'), {});
    }
    else if(props.from == 'DayEndDetail'){
        form.get(route('getDayendDetailReport'), {});
    }
    else if(props.from == 'ProfitLoss'){
        form.get(route('getpalReport'), {});
    }
    else if(props.from == 'ProductWiseSale'){
        form.get(route('getProductWiseSaleReport'), {});
    }
    else if(props.from == 'BrandWiseSale'){
        form.get(route('getBrandWiseSaleReport'), {});
    }
    
};

</script>

<template>

    <Head :title="from" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">{{ from }} Report</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-5">
                                    <InputLabel for="from_" value="From" />

                                    <TextInput id="from_" type="date" class="mt-1 block w-full"
                                        v-model="form.from_" required autofocus placeholder="Enter From Date" />

                                    <InputError class="mt-2" :message="form.errors.from_" />
                                </div>
                                <div class="col-md-5">
                                    <InputLabel for="to" value="To" />

                                    <TextInput id="to" type="date" class="mt-1 block w-full"
                                        v-model="form.to" required placeholder="Enter To Date" />

                                    <InputError class="mt-2" :message="form.errors.to" />
                                </div>
                                <div class="col-md-2">
                                    <PrimaryButton class="btn btn-primary mt-8">
                                        Generate
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
