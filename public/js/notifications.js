// Ajout de débogage dans le fichier notifications.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de notifications chargé');

    // Fonction pour mettre à jour le nombre de notifications non lues et la liste
    function updateNotifications() {
        console.log('Vérification des nouvelles notifications...');
        fetch('/get-new-notifications')
            .then(response => {
                console.log('Réponse reçue:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Données reçues:', data);
                
                // Mettre à jour le badge
                const badge = document.getElementById('notificationBadge');
                if (data.unreadCount > 0) {
                    if (badge) {
                        console.log('Mise à jour du badge:', data.unreadCount);
                        badge.textContent = data.unreadCount;
                    } else {
                        console.log('Création du badge:', data.unreadCount);
                        const newBadge = document.createElement('span');
                        newBadge.id = 'notificationBadge';
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = data.unreadCount;
                        
                        const iconWrapper = document.querySelector('.notification-icon-wrapper');
                        if (iconWrapper) {
                            iconWrapper.appendChild(newBadge);
                        } else {
                            console.error('Élément .notification-icon-wrapper non trouvé');
                        }
                    }
                } else if (badge) {
                    console.log('Suppression du badge car aucune notification non lue');
                    badge.remove();
                }
                
                // Mettre à jour la liste des notifications si le menu déroulant est ouvert
                const dropdownMenu = document.querySelector('.dropdown-menu.show');
                if (dropdownMenu) {
                    console.log('Menu déroulant ouvert, mise à jour de la liste');
                    updateNotificationList(data.notifications);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des notifications:', error);
            });
    }

    // Fonction pour mettre à jour la liste des notifications
    function updateNotificationList(notifications) {
        console.log('Mise à jour de la liste des notifications:', notifications);
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        if (!dropdownMenu) {
            console.error('Élément .dropdown-menu non trouvé');
            return;
        }
        
        // Vider le menu déroulant
        dropdownMenu.innerHTML = '';
        
        if (!notifications || notifications.length === 0) {
            console.log('Aucune notification à afficher');
            dropdownMenu.innerHTML = `
                <div class="dropdown-item text-center">
                    <p class="text-muted mb-0">Aucune notification</p>
                </div>
            `;
        } else {
            console.log(`Affichage de ${notifications.length} notifications`);
            notifications.forEach((notification, index) => {
                const notificationItem = document.createElement('div');
                notificationItem.className = `dropdown-item ${!notification.isRead ? 'unread-notification' : ''}`;
                notificationItem.id = `notification-${notification.id}`;
                
                notificationItem.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img class="rounded-circle6 me-lg-2" src="/uploads/${notification.image}" alt="Image">
                        <div class="ms-2">
                            <div class="notification-text">${notification.message}</div>
                            <small class="text-muted">${notification.createdAt}</small>
                        </div>
                    </div>
                `;
                
                dropdownMenu.appendChild(notificationItem);
                
                // Ajouter un séparateur sauf pour le dernier élément
                if (index < notifications.length - 1) {
                    const divider = document.createElement('hr');
                    divider.className = 'dropdown-divider';
                    dropdownMenu.appendChild(divider);
                }
            });
        }
    }

    // Obtenir les nouvelles notifications toutes les 5 secondes
    console.log('Configuration de la vérification périodique des notifications');
    setInterval(updateNotifications, 5000);
    
    // Mettre à jour la liste des notifications lorsque l'utilisateur ouvre le menu déroulant
    const dropdownToggle = document.getElementById('notificationDropdown');
    if (dropdownToggle) {
        console.log('Ajout du gestionnaire d\'événements pour l\'ouverture du menu déroulant');
        dropdownToggle.addEventListener('click', function() {
            console.log('Menu déroulant cliqué');
            fetch('/get-new-notifications')
                .then(response => response.json())
                .then(data => {
                    console.log('Données reçues pour le menu déroulant:', data);
                    updateNotificationList(data.notifications);
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des notifications pour le menu déroulant:', error);
                });
        });
    } else {
        console.error('Élément #notificationDropdown non trouvé');
    }
    
    // Initialiser au chargement
    console.log('Initialisation des notifications au chargement');
    updateNotifications();
});