// assets/js/payment.js

/**
 * LGN E-Learning - Payment JavaScript (Midtrans Integration)
 */

const Payment = {
    
    /**
     * Initialize payment for a course
     */
    async initPayment(courseId, couponCode = null) {
        try {
            LGN.Loading.show('Mempersiapkan pembayaran...');
            
            const response = await LGN.API.post('/api/payment/create_transaction.php', {
                course_id: courseId,
                coupon_code: couponCode
            });
            
            LGN.Loading.hide();
            
            if (response.success && response.data.snap_token) {
                // Open Midtrans Snap popup
                this.openSnapPopup(response.data.snap_token, response.data.order_id);
            } else {
                LGN.Toast.error(response.message || 'Gagal memulai pembayaran');
            }
        } catch (error) {
            LGN.Loading.hide();
            LGN.Toast.error(error.message || 'Terjadi kesalahan');
        }
    },
    
    /**
     * Open Midtrans Snap popup
     */
    openSnapPopup(snapToken, orderId) {
        if (typeof snap === 'undefined') {
            LGN.Toast.error('Payment gateway tidak tersedia');
            return;
        }
        
        snap.pay(snapToken, {
            onSuccess: (result) => {
                console.log('Payment success:', result);
                this.handlePaymentSuccess(orderId, result);
            },
            onPending: (result) => {
                console.log('Payment pending:', result);
                this.handlePaymentPending(orderId, result);
            },
            onError: (result) => {
                console.log('Payment error:', result);
                this.handlePaymentError(orderId, result);
            },
            onClose: () => {
                console.log('Payment popup closed');
                this.handlePaymentClose(orderId);
            }
        });
    },
    
    /**
     * Handle successful payment
     */
    async handlePaymentSuccess(orderId, result) {
        try {
            LGN.Loading.show('Memverifikasi pembayaran...');
            
            const response = await LGN.API.post('/api/payment/verify.php', {
                order_id: orderId,
                transaction_id: result.transaction_id,
                payment_type: result.payment_type
            });
            
            LGN.Loading.hide();
            
            if (response.success) {
                LGN.Toast.success('Pembayaran berhasil!');
                setTimeout(() => {
                    window.location.href = `/pages/payment_success.php?order_id=${orderId}`;
                }, 1000);
            }
        } catch (error) {
            LGN.Loading.hide();
            // Still redirect to success page, let server verify
            window.location.href = `/pages/payment_success.php?order_id=${orderId}`;
        }
    },
    
    /**
     * Handle pending payment
     */
    handlePaymentPending(orderId, result) {
        LGN.Toast.info('Menunggu pembayaran...');
        window.location.href = `/pages/payment_pending.php?order_id=${orderId}`;
    },
    
    /**
     * Handle payment error
     */
    handlePaymentError(orderId, result) {
        LGN.Toast.error('Pembayaran gagal. Silakan coba lagi.');
    },
    
    /**
     * Handle popup close without completing payment
     */
    async handlePaymentClose(orderId) {
        // Check payment status
        try {
            const response = await LGN.API.get(`/api/payment/check_status.php?order_id=${orderId}`);
            
            if (response.data.status === 'success') {
                window.location.href = `/pages/payment_success.php?order_id=${orderId}`;
            } else if (response.data.status === 'pending') {
                LGN.Toast.warning('Pembayaran belum selesai. Anda dapat melanjutkan dari halaman transaksi.');
            }
        } catch (error) {
            console.error('Error checking payment status:', error);
        }
    },
    
    /**
     * Apply coupon code
     */
    async applyCoupon(courseId, couponCode) {
        try {
            const response = await LGN.API.post('/api/payment/apply_coupon.php', {
                course_id: courseId,
                coupon_code: couponCode
            });
            
            if (response.success) {
                return response.data;
            } else {
                LGN.Toast.error(response.message || 'Kode kupon tidak valid');
                return null;
            }
        } catch (error) {
            LGN.Toast.error(error.message || 'Gagal menerapkan kupon');
            return null;
        }
    }
};

// =====================================================
// CHECKOUT PAGE FUNCTIONALITY
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    
    // Pay button
    const payButton = document.getElementById('payButton');
    if (payButton) {
        payButton.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            const couponInput = document.getElementById('couponCode');
            const couponCode = couponInput ? couponInput.value.trim() : null;
            
            Payment.initPayment(courseId, couponCode);
        });
    }
    
    // Apply coupon button
    const applyCouponBtn = document.getElementById('applyCouponBtn');
    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', async function() {
            const courseId = this.dataset.courseId;
            const couponInput = document.getElementById('couponCode');
            const couponCode = couponInput.value.trim();
            
            if (!couponCode) {
                LGN.Toast.warning('Masukkan kode kupon');
                return;
            }
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            const result = await Payment.applyCoupon(courseId, couponCode);
            
            this.disabled = false;
            this.innerHTML = 'Terapkan';
            
            if (result) {
                // Update price display
                const discountRow = document.getElementById('discountRow');
                const discountAmount = document.getElementById('discountAmount');
                const totalPrice = document.getElementById('totalPrice');
                
                if (discountRow && discountAmount && totalPrice) {
                    discountRow.style.display = 'flex';
                    discountAmount.textContent = '- ' + LGN.formatCurrency(result.discount);
                    totalPrice.textContent = LGN.formatCurrency(result.final_price);
                }
                
                LGN.Toast.success(`Kupon berhasil diterapkan! Diskon ${LGN.formatCurrency(result.discount)}`);
            }
        });
    }
});

// Export
window.Payment = Payment;