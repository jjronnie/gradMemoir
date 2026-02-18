<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        confirmText?: string;
        cancelText?: string;
        confirmVariant?: 'default' | 'destructive';
        processing?: boolean;
    }>(),
    {
        confirmText: 'Proceed',
        cancelText: 'Cancel',
        confirmVariant: 'destructive',
        processing: false,
    },
);

const emit = defineEmits<{
    (event: 'update:open', value: boolean): void;
    (event: 'confirm'): void;
}>();

const close = (): void => {
    emit('update:open', false);
};

const confirm = (): void => {
    emit('confirm');
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="processing"
                    @click="close"
                >
                    {{ cancelText }}
                </Button>
                <Button
                    type="button"
                    :variant="confirmVariant"
                    :disabled="processing"
                    @click="confirm"
                >
                    {{ processing ? 'Processing...' : confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
