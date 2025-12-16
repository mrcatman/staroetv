export type RouteParams = {
    "index": {};
    "admin.": {};
    "admin.actions-logs.index": {};
    "admin.channels.index": {};
    "admin.channels.order.index": {};
    "admin.channels.order.save": {};
    "admin.crossposting": {};
    "admin.genres.index": {};
    "admin.genres.save": {};
    "admin.pages.index": {};
    "admin.permissions.index": {};
    "admin.permissions.save": {};
    "admin.smiles.index": {};
    "admin.smiles.save": {};
    "admin.user-groups.index": {};
    "admin.user-groups.store": {};
    "admin.user-groups.create": {};
    "admin.user-groups.show": {
        user_group: string;
    };
    "admin.user-groups.update": {
        user_group: string;
    };
    "admin.user-groups.destroy": {
        user_group: string;
    };
    "admin.user-groups.edit": {
        user_group: string;
    };
    "admin.users.index": {};
    "admin.users.change-group": {};
    "admin.users.change-password": {};
    "admin.users.delete": {};
    "admin.users.reputation": {};
    "articles.index": {};
    "articles.get-actions": {};
    "articles.add": {};
    "articles.save": {};
    "articles.approve": {};
    "articles.change-type": {};
    "articles.get-crosspost-parameters": {};
    "articles.crosspost": {};
    "articles.delete": {};
    "articles.show": {
        id: string;
    };
    "articles.edit": {
        id: string;
    };
    "articles.update": {
        id: string;
    };
    "awards.ajax": {};
    "awards.delete": {};
    "awards.edit": {};
    "awards.form": {};
    "awards.create": {};
    "channels.add": {};
    "channels.save": {};
    "channels.ajax": {};
    "channels.approve": {};
    "channels.autocomplete": {};
    "channels.delete": {};
    "channels.merge": {};
    "channels.show": {
        id: string;
    };
    "design.channels.all": {
        id: string;
    };
    "design.channels.add": {
        id: string;
    };
    "design.channels.save": {
        id: string;
    };
    "design.channels.ajax": {
        id: string;
    };
    "design.channels.edit": {
        id: string;
        package_id: string;
    };
    "design.channels.update": {
        id: string;
        package_id: string;
    };
    "design.channels.show": {
        id: string;
        package_id: string;
    };
    "channels.edit": {
        id: string;
    };
    "channels.update": {
        id: string;
    };
    "channels.programs.ajax": {
        id: string;
    };
    "channels.programs.edit-list": {
        id: string;
    };
    "channels.programs.save-list": {
        id: string;
    };
    "comments.add": {};
    "comments.ajax": {};
    "comments.delete": {};
    "comments.edit": {};
    "comments.rating": {};
    "profile.confirm": {
        code: string;
    };
    "contact.index": {};
    "contact.send": {};
    "crosspostAutoconnect": {
        name: string;
    };
    "crosspostRedirectUri": {
        name: string;
    };
    "crosspostSaveSettings": {
        name: string;
    };
    "cut.download-external": {};
    "cut.on-downloaded": {
        id: string;
    };
    "cut.show-form": {
        id: string;
    };
    "cut.start": {
        id: string;
    };
    "cut.show": {
        id: string;
    };
    "cut.save": {
        id: string;
    };
    "cut.make-video": {
        id: string;
        index: string;
    };
    "design.delete": {};
    "records.embed": {
        id: string;
    };
    "events.index": {};
    "events.add": {};
    "events.save": {};
    "events.approve": {};
    "events.delete": {};
    "events.show": {
        id: string;
    };
    "events.edit": {
        id: string;
    };
    "events.update": {
        id: string;
    };
    "profile.forgot-password": {};
    "profile.forgot-password-send": {};
    "forum.index": {};
    "forum.redirect-to-message-by-id": {
        message_id: string;
    };
    "forum.messages.delete": {};
    "forum.topics.delete": {};
    "forum.messages.update": {};
    "forum.topics.edit": {
        id: string;
    };
    "forum.topics.save": {
        id: string;
    };
    "forum.subforums.edit": {
        id: string;
    };
    "forum.subforums.save": {
        id: string;
    };
    "forum.get-edit-form": {};
    "forum.last-topics": {};
    "forum.topics.move": {};
    "forum.subforums.create": {};
    "forum.messages.create": {};
    "forum.profile": {
        id: string;
    };
    "forum.user-messages": {
        user_id: string;
    };
    "forum.topics.show": {
        forum_id: string;
        topic_id: string;
    };
    "forum.topics.show-last-message": {
        forum_id: string;
        topic_id: string;
    };
    "forum.topics.redirect-to-message-with-page": {
        forum_id: string;
        topic_id: string;
        message_id: string;
        page_id: string;
        time: string;
    };
    "forum.topics.redirect-to-message": {
        forum_id: string;
        topic_id: string;
        message_id: string;
        time: string;
    };
    "forum.topics.show-page": {
        forum_id: string;
        topic_id: string;
        page_id: string;
    };
    "forum.subforums.show": {
        id: string;
    };
    "forum.subforums.new": {
        id: string;
    };
    "forum.topics.new": {
        id: string;
    };
    "forum.topics.create": {
        id: string;
    };
    "pages.show": {
        id: string;
    };
    "users.index": {};
    "users.show-me": {};
    "users.show-by-username": {
        username: string;
    };
    "login": {};
    "logout": {};
    "mass-upload.index": {};
    "mass-upload.list": {};
    "mass-upload.from-device": {};
    "comments.latest": {};
    "pages.index": {};
    "pages.add": {};
    "pages.save": {};
    "pages.delete": {};
    "pages.edit": {
        id: string;
    };
    "pages.update": {
        id: string;
    };
    "pages.show-by-url": {
        url: string;
    };
    "password.email": {};
    "password.update": {};
    "password.request": {};
    "password.reset": {
        token: string;
    };
    "pm.index": {};
    "pm.add": {};
    "pm.save": {};
    "pm.cancel": {};
    "pm.delete": {};
    "pm.show": {
        id: string;
    };
    "pm.edit": {
        id: string;
    };
    "pm.update": {
        id: string;
    };
    "profile.edit": {};
    "profile.save": {};
    "profile.edit.user": {
        id: string;
    };
    "profile.notifications": {};
    "profile.edit-password": {};
    "profile.save-password": {};
    "profile.telegram.connect": {};
    "profile.telegram.disconnect": {};
    "profile.telegram.register-form": {};
    "profile.telegram.register": {};
    "programs.add": {};
    "programs.save": {};
    "programs.approve": {};
    "programs.autocomplete": {};
    "programs.delete": {};
    "programs.merge": {};
    "programs.show": {
        id: string;
    };
    "design.programs.show": {
        id: string;
    };
    "design.programs.add": {
        id: string;
    };
    "design.programs.save": {
        id: string;
    };
    "design.programs.ajax": {
        id: string;
    };
    "design.programs.edit": {
        id: string;
        package_id: string;
    };
    "design.programs.update": {
        id: string;
        package_id: string;
    };
    "programs.edit": {
        id: string;
    };
    "programs.update": {
        id: string;
    };
    "forum.questionnaire.form": {};
    "forum.questionnaire.vote": {};
    "records.radio.index": {};
    "design.programs.radio-stations": {};
    "radio-stations.add": {};
    "radio-stations.save": {};
    "radio-stations.ajax": {};
    "radio-stations.approve": {};
    "radio-stations.autocomplete": {};
    "radio-stations.delete": {};
    "radio-stations.merge": {};
    "radio-stations.show": {
        id: string;
    };
    "design.radio-stations.all": {
        id: string;
    };
    "design.radio-stations.add": {
        id: string;
    };
    "design.radio-stations.save": {
        id: string;
    };
    "design.radio-stations.ajax": {
        id: string;
    };
    "design.radio-stations.edit": {
        id: string;
        package_id: string;
    };
    "design.radio-stations.update": {
        id: string;
        package_id: string;
    };
    "design.radio-stations.show": {
        id: string;
        package_id: string;
    };
    "radio-stations.edit": {
        id: string;
    };
    "radio-stations.update": {
        id: string;
    };
    "radio-stations.programs.ajax": {
        id: string;
    };
    "radio-stations.programs.edit-list": {
        id: string;
    };
    "radio-stations.programs.save-list": {
        id: string;
    };
    "records.radio.add": {};
    "records.radio.calendar.index": {};
    "records.radio.calendar.year": {
        year: string;
    };
    "records.radio.calendar.month": {
        year: string;
        month: string;
    };
    "records.radio.commercials": {};
    "records.radio.commercials-search": {};
    "design.radio-stations.index": {};
    "records.radio.other": {};
    "records.radio.other.category": {
        category: string;
    };
    "records.radio.programs": {};
    "records.radio.programs.ajax": {};
    "records.radio.search": {};
    "records.radio.show": {
        id: string;
    };
    "records.radio.edit": {
        id: string;
    };
    "records.save": {};
    "records.after-upload": {};
    "records.ajax": {};
    "records.approve": {};
    "records.categories": {};
    "records.delete": {};
    "records.download": {};
    "records.get-info": {};
    "records.mass-edit": {};
    "records.playlist-ajax": {
        id: string;
    };
    "records.screenshot": {};
    "records.search": {};
    "records.set-telegram-id": {};
    "records.upload": {};
    "records.update": {
        id: string;
    };
    "admin.redactor-panel": {};
    "register": {};
    "reputation.ajax": {};
    "reputation.change": {};
    "reputation.delete": {};
    "reputation.edit": {};
    "reputation.reply": {};
    "site-search": {};
    "smiles.ajax": {};
    "contact.digitization.index": {};
    "contact.digitization.send": {};
    "pages.team": {};
    "teletext.index": {};
    "teletext.add": {};
    "teletext.save": {};
    "teletext.approve": {};
    "teletext.channel": {
        id: string;
    };
    "teletext.delete": {};
    "teletext.show": {
        id: string;
    };
    "teletext.edit": {
        id: string;
    };
    "teletext.update": {
        id: string;
    };
    "top-list.articles": {};
    "top-list.awards": {};
    "top-list.comments": {};
    "top-list.forum": {};
    "top-list.news": {};
    "top-list.radio-recordings": {};
    "top-list.reputation": {};
    "top-list.videos": {};
    "pictures.upload": {};
    "pictures.upload-by-url": {};
    "pictures.get-by-channel": {
        id: string;
    };
    "users.autocomplete": {};
    "profile.change-email": {
        code: string;
    };
    "users.show": {
        id: string;
    };
    "comments.user": {
        id: string;
    };
    "users.radio-recordings": {
        id: string;
    };
    "users.videos": {
        id: string;
    };
    "records.video.index": {};
    "records.video.add": {};
    "records.video.calendar.index": {};
    "records.video.calendar.year": {
        year: string;
    };
    "records.video.calendar.month": {
        year: string;
        month: string;
    };
    "records.video.commercials": {};
    "records.video.commercials-search": {};
    "design.channels.index": {};
    "design.programs.channels": {};
    "records.video.other": {};
    "records.video.other.category": {
        category: string;
    };
    "records.video.programs": {};
    "records.video.programs.ajax": {};
    "records.video.search": {};
    "records.video.show": {
        id: string;
    };
    "records.video.edit": {
        id: string;
    };
    "warnings.add": {};
    "warnings.ajax": {};
    "warnings.form": {};
};
