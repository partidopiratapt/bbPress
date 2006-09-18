addLoadEvent( function() { // Posts
	thePostList = new listMan('thread');
	thePostList.alt = 'alt';
	thePostList.altOffset = 1;
} );

function ajaxPostDelete(postId, postAuthor) {
	if (!confirm('Are you sure you wanna delete this post by "' + postAuthor + '"?')) return false;
	return thePostList.ajaxDelete( 'post', postId );
}

function newPostAddIn() { // Not currently loaded
	var postFormSub = $('postformsub');
	if ( postFormSub )
		postFormSub.onclick = function(e) { return thePostList.ajaxAdder( 'post', 'postform' ); }
}

addLoadEvent( function() { // Tags
	var newtag = $('tag');
	if (!newtag)
		return;
	newtag.setAttribute('autocomplete', 'off');

	yourTagList = new listMan('yourtaglist');
	yourTagList.alt = false;
	yourTagList.showLink = false;
	yourTagList.inputData = '&topic_id=' + topicId;
	othersTagList = new listMan('otherstaglist');
	othersTagList.alt = false;
	othersTagList.inputData = '&topic_id=' + topicId;

	if ( !yourTagList.theList )
		return;
	var newtagSub = $('tagformsub');
	newtagSub.onclick = function(e) { return yourTagList.ajaxAdder( 'tag', 'tag-form' ); }
} );

function ajaxDelTag(tag, user, tagName) {
	if ( !confirm('Are you sure you want to remove the "' + tagName + '" tag?') )
		return false;
	if ( currentUserId == user )
		return yourTagList.ajaxDelete( 'tag', tag + '_' + user );
	else
		return othersTagList.ajaxDelete( 'tag', tag + '_' + user );
}

addLoadEvent( function() { // TopicMeta
	theTopicMeta = new listMan('topicmeta');
	theTopicMeta.showLink = false;
	theTopicMeta.inputData = '&user_id=' + currentUserId + '&topic_id=' + topicId;
	theTopicMeta.dimComplete = function(what, id, dimClass) {
		if ( 'is-not-favorite' == dimClass ) {
			var favoritesToggle = $('favorite-toggle');
			isFav = favoritesToggle.hasClassName(dimClass) ? 0 : 1;
			favLinkSetup();
		}
	}
	favLinkSetup();
			
	var resolvedSub = $('resolvedformsub');
	if ( !resolvedSub )
		return;
	resFunc = function(e) { return theTopicMeta.ajaxUpdater( 'resolution', 'resolved' ); }
	resolvedSub.onclick = resFunc;
	theTopicMeta.addComplete = function(what, where, update) {
		if ( update && 'resolved' == where )
			$('resolvedformsub').onclick = resFunc;
	}
} );

function favLinkSetup() {
	var favoritesToggle = $('favorite-toggle');
	if ('no' == isFav)
		return;
	if ( 1 == isFav )
		favoritesToggle.update('This topic is one of your <a href="' + favoritesLink + '">favorites</a> [<a href="#" onclick="return FavIt();">x</a>]');
	else 
		favoritesToggle.update('<a href="#" onclick="return FavIt();">Add this topic to your favorites</a> (<a href="' + favoritesLink + '">?</a>)');
}

function FavIt() { return theTopicMeta.ajaxDimmer( 'favorite', 'toggle', 'is-not-favorite' ); }
