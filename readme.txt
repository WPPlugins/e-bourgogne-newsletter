=== e-bourgogne Newsletter===
Contributors: www.e-bourgogne.fr
Tags: e-bourgogne, bourgogne, newsletter, newsletters, mon site internet
Requires at least: 3.3
Tested up to: 4.2.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Permet à vos utilisateurs de s'inscrire à vos Newsletters e-bourgogne depuis votre site.

== Description ==

Ce plugin vous permettra d'ajouter à vos pages un widget permettant aux utilisateurs de s'inscrire aux Newsletters [e-bourgogne](www.e-bourgogne.fr) de votre choix. Il gère également le lien de désincription proposé à chaque utilisateur dans les newsletters : lors d'une désincriptions, une page de votre site confirmera la désincription à l'utilisateur, avant de le rediriger vers l'accueil de votre site.

== Installation ==

1. Installez le plugin depuis l'outil de gestion des extensions de Wordpress et activez-le. Vous pouvez également installer le plugin manuellement en extrayant les fichiers du plugin dans le dossier `wp-content/plugins/e-bourgogne-newsletter`. 
1. Allez dans le menu de configuration "e-bourgogne" (ou "e-bourgogne->Newsletter" si vous avez déjà installé un autre module e-bourgogne) et renseignez votre clé d'API e-bourgogne dans le champ prévu à cette effet (veuillez contacter l'administrateur de votre organisme pour connaître votre clé) . Si un autre module e-bourgogne est déjà configuré, la même clé sera utilisée pour tout les modules.
1. Allez dans le menu "Apparence->Widgets" pour configurer le widget Newsletter e-bourgogne.

_Note_ : les modules e-bourgogne nécessitent l'activation de l'extension `cURL` pour PHP


== Frequently Asked Questions ==

= Comment intégrer l'inscription à mes newsletters à mon site ? =
Le plugin Newsletter d'e-bourgogne propose un widget que vous pouvez ajouter à vos pages. Pour ajouter un widget Newsletter e-bourgogne, rendez-vous sur l'écran "Apparence->Widgets", puis ajouter le widget e-bourgogne Newsletter à la zone de widgets que vous souhaitez. Lorsque vous ajouter ce widget, vous pouvez lui donner un titre, un court texte de description et choisir la ou les newsletter qui seront proposés à l'inscription. Vous pouvez suivre les inscriptions dans le service "Administration de mon site->Newsletter->Fichiers de destinataires" d'[e-bourgogne](www.e-bourgogne.fr).

= Comment gérer la déscription des utilisateurs à mes newsletter ? =
Chaque newsletter envoyée contient un lien en fin de message permettant à un utilisateur de se désinscrire. S'il clique dessus, il sera dirigé vers une page de votre site configurée automatiquement par le plugin lui confirmant sa déscription. Il est ensuite automatiquement redirigé vers l'accueil de votre site.

= Comment configurer mes fichiers de destinataires pour mes newsletter ? =
La configuration des fichiers de destinataires se fait via e-bourgogne, dans le service "Administration de mon site->Newsletter->Fichiers de destinataires". 

= Où puis-je récupérer ma clé d'API ? =
La clé d'API permettant d'accéder aux services e-bourgogne via les plugins doit vous être fournie par l'administrateur de votre organisme. Elle doit ensuite être renseignée dans le panneau de configuration du plugin e-bourgogne. Note : la clé est partagée par tout les plugins e-bourgogne ; si plusieurs plugins e-bourgogne sont installés, modifier la clé pour l'un d'entre eux la modifiera pour tout les autres également.

= Comment savoir si ma clé d'API est correcte ? =
Lorsque vous affichez le panneau de configuration de votre plugin e-bourgogne, vous verrez un champ "Clé d'API" en haut de la page. Inscrivez-y votre clé, puis cliquez sur "Enregistrer la clé". Si celle-ci est valide, vous verrez une coche verte apparaître à côté du champ. Dans le cas contraire, un message vous indiquera que votre clé est incorrecte.

== Screenshots ==

== Changelog ==

= 1.0.1 =
* Ajout d'un avertissement en cas d'absence de cURL PHP

= 1.0 =
* Ajout d'un widget Newsletter e-bourgogne pour l'inscription d'utilisateurs
* Ajout d'une page de confirmation de désinscription et de redirection vers l'accueil
