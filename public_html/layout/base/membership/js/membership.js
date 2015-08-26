Membership = function()
{
    /**
     * Server url
     * @var string
     */
    var serverUrl;

    /**
     * Confrim title
     * @var string
     */
    var confirmTitle;

    /**
     * Cancel title
     * @var string
     */
    var cancelTitle;

    /**
     * Container Id
     * @var string
     */
    var containerId;

    /**
     * Current object
     * @var object
     */
    var self = this;

    /**
     * Delete purchased membership level
     *
     * @param object link
     * @return void
     */
    this.deletePurchasedMembership = function(link)
    {
        showConfirmPopup(confirmTitle, cancelTitle, link, function(){
            // send a delete query
            ajaxQuery(containerId, serverUrl, '', 'post', {action: 'delete_membership', id: $(link).attr('membership-id')});
        });
    }

    /**
     * Set server url
     *
     * @param sting url
     * @return Membership - fluent interface
     */
    this.setServerUrl = function(url)
    {
        serverUrl = url;

        return this;
    }

    /**
     * Set confirm title
     *
     * @param sting title
     * @return Membership - fluent interface
     */
    this.setConfirmTitle = function(title)
    {
        confirmTitle = title;

        return this;
    }

    /**
     * Set cancel title
     *
     * @param sting title
     * @return Membership - fluent interface
     */
    this.setCancelTitle = function(title)
    {
        cancelTitle = title;

        return this;
    }

    /**
     * Set container
     *
     * @param sting container
     * @return Membership - fluent interface
     */
    this.setContainer = function(container)
    {
        containerId = container;

        return this;
    }
}